
import '@tensorflow/tfjs-backend-webgl';
import '@tensorflow/tfjs-backend-webgpu';

import * as mpPose from '@mediapipe/pose';
import * as tfjsWasm from '@tensorflow/tfjs-backend-wasm';
import * as tf from '@tensorflow/tfjs-core';
import * as posedetection from '@tensorflow-models/pose-detection';

import {Camera} from '@pose/camera';
import {RendererWebGPU} from '@pose/renderer_webgpu';
import {RendererCanvas2d} from '@pose/renderer_canvas2d';
import {setupDatGui} from '@pose/option_panel';
import {STATE} from '@pose/params';
import {setupStats} from '@pose/stats_panel';
import {setBackendAndEnvFlags} from '@pose/util';

let detector;
let camera;
let stats;
let startInferenceTime;
let numInferences = 0;
let inferenceTimeSum = 0;
let lastPanelUpdate = 0;
let rafId;
let renderer = null;
let useGpuRenderer = false;
let repCount = {left: 0, right: 0};
let currentState = {left: 'down', right: 'down'};
const repCounterElement = document.querySelector('#rep-counter');
repCounterElement.innerHTML = `
  <div class="arm-counter">
    <div class="label">Left Arm</div>
    <div class="count" id="left-count">0</div>
    <div class="angle" id="left-angle">0°</div>
  </div>
  <div class="arm-counter">
    <div class="label">Right Arm</div>
    <div class="count" id="right-count">0</div>
    <div class="angle" id="right-angle">0°</div>
  </div>
`;
const leftCountElement = document.getElementById('left-count');
const rightCountElement = document.getElementById('right-count');
const leftAngleElement = document.getElementById('left-angle');
const rightAngleElement = document.getElementById('right-angle');
const loadingElement = document.getElementById('loading');
const canvasWrapper = document.querySelector('.canvas-wrapper');
const repSound = new Audio('/build/sounds/beep.mp3');

async function createDetector() {
  switch (STATE.model) {
    case posedetection.SupportedModels.PoseNet:
      return posedetection.createDetector(STATE.model, {
        quantBytes: 4,
        architecture: 'MobileNetV1',
        outputStride: 16,
        inputResolution: {width: 500, height: 500},
        multiplier: 0.75,
      });
    case posedetection.SupportedModels.BlazePose:
      const runtime = STATE.backend.split('-')[0];
      if (runtime === 'mediapipe') {
        return posedetection.createDetector(STATE.model, {
          runtime,
          modelType: STATE.modelConfig.type,
          solutionPath:
              `https://cdn.jsdelivr.net/npm/@mediapipe/pose@${mpPose.VERSION}`,
        });
      } else if (runtime === 'tfjs') {
        return posedetection.createDetector(
            STATE.model, {runtime, modelType: STATE.modelConfig.type});
      }
    case posedetection.SupportedModels.MoveNet:
      let modelType;
      if (STATE.modelConfig.type == 'lightning') {
        modelType = posedetection.movenet.modelType.SINGLEPOSE_LIGHTNING;
      } else if (STATE.modelConfig.type == 'thunder') {
        modelType = posedetection.movenet.modelType.SINGLEPOSE_THUNDER;
      } else if (STATE.modelConfig.type == 'multipose') {
        modelType = posedetection.movenet.modelType.MULTIPOSE_LIGHTNING;
      }
      const modelConfig = {modelType};

      if (STATE.modelConfig.customModel !== '') {
        modelConfig.modelUrl = STATE.modelConfig.customModel;
      }
      if (STATE.modelConfig.type === 'multipose') {
        modelConfig.enableTracking = STATE.modelConfig.enableTracking;
      }
      return posedetection.createDetector(STATE.model, modelConfig);
  }
}

async function checkGuiUpdate() {
  if (STATE.isTargetFPSChanged || STATE.isSizeOptionChanged) {
    camera = await Camera.setup(STATE.camera);
    STATE.isTargetFPSChanged = false;
    STATE.isSizeOptionChanged = false;
  }

  if (STATE.isModelChanged || STATE.isFlagChanged || STATE.isBackendChanged) {
    STATE.isModelChanged = true;

    window.cancelAnimationFrame(rafId);

    if (detector != null) {
      detector.dispose();
    }

    if (STATE.isFlagChanged || STATE.isBackendChanged) {
      await setBackendAndEnvFlags(STATE.flags, STATE.backend);
    }

    try {
      detector = await createDetector(STATE.model);
    } catch (error) {
      detector = null;
      alert(error);
    }

    STATE.isFlagChanged = false;
    STATE.isBackendChanged = false;
    STATE.isModelChanged = false;
  }
}

function beginEstimatePosesStats() {
  startInferenceTime = (performance || Date).now();
}

function endEstimatePosesStats() {
  const endInferenceTime = (performance || Date).now();
  inferenceTimeSum += endInferenceTime - startInferenceTime;
  ++numInferences;

  const panelUpdateMilliseconds = 1000;
  if (endInferenceTime - lastPanelUpdate >= panelUpdateMilliseconds) {
    const averageInferenceTime = inferenceTimeSum / numInferences;
    inferenceTimeSum = 0;
    numInferences = 0;
    stats.customFpsPanel.update(
        1000.0 / averageInferenceTime, 120 /* maxValue */);
    lastPanelUpdate = endInferenceTime;
  }
}

async function renderResult() {
  if (camera.video.readyState < 2) {
    await new Promise((resolve) => {
      camera.video.onloadeddata = () => {
        resolve(video);
      };
    });
  }

  let poses = null;
  let canvasInfo = null;

  // Detector can be null if initialization failed (for example when loading
  // from a URL that does not exist).
  if (detector != null) {
    // FPS only counts the time it takes to finish estimatePoses.
    beginEstimatePosesStats();

    if (useGpuRenderer && STATE.model !== 'PoseNet') {
      throw new Error('Only PoseNet supports GPU renderer!');
    }
    // Detectors can throw errors, for example when using custom URLs that
    // contain a model that doesn't provide the expected output.
    try {
      if (useGpuRenderer) {
        console.log('estimatePosesGPU');
        const [posesTemp, canvasInfoTemp] = await detector.estimatePosesGPU(
            camera.video,
            {maxPoses: STATE.modelConfig.maxPoses, flipHorizontal: false},
            true);
        poses = posesTemp;
        canvasInfo = canvasInfoTemp;
      } else {
        poses = await detector.estimatePoses(
            camera.video,
            {maxPoses: STATE.modelConfig.maxPoses, flipHorizontal: false});
      }
    } catch (error) {
      detector.dispose();
      detector = null;
      alert(error);
    }

    endEstimatePosesStats();
  }
  const rendererParams = useGpuRenderer ?
      [camera.video, poses, canvasInfo, STATE.modelConfig.scoreThreshold] :
      [camera.video, poses, STATE.isModelChanged];
  checkForRep(poses);
  renderer.draw(rendererParams);
}

// Helper function to trigger rep completion effects
function triggerRepEffects() {
  // Play sound
  repSound.currentTime = 0;
  repSound.play().catch((error) => {
    console.warn('Error playing sound:', error);
  });

  // Trigger glow animation
  canvasWrapper.classList.add('rep-complete');
  canvasWrapper.addEventListener('animationend', () => {
    canvasWrapper.classList.remove('rep-complete');
  }, {once: true});
}

// Helper function to calculate angle between three points
function calculateAngle(a, b, c) {
  const ab = Math.sqrt(Math.pow(b.x - a.x, 2) + Math.pow(b.y - a.y, 2));
  const bc = Math.sqrt(Math.pow(b.x - c.x, 2) + Math.pow(b.y - c.y, 2));
  const ac = Math.sqrt(Math.pow(c.x - a.x, 2) + Math.pow(c.y - a.y, 2));
  const angle = Math.acos((ab * ab + bc * bc - ac * ac) / (2 * ab * bc));
  return angle * (180 / Math.PI); // Convert to degrees
}

function checkForRep(poses) {
  if (!poses || !poses[0] || !poses[0].keypoints || !poses[0].keypoints.length) {
    console.info('No keypoints found');
    return;
  }

  const keypoints = poses[0].keypoints;

  // Get all required keypoints
  const leftWrist = keypoints.find((point) => point.name === 'left_wrist');
  const leftElbow = keypoints.find((point) => point.name === 'left_elbow');
  const leftShoulder = keypoints.find((point) => point.name === 'left_shoulder');
  const rightWrist = keypoints.find((point) => point.name === 'right_wrist');
  const rightElbow = keypoints.find((point) => point.name === 'right_elbow');
  const rightShoulder = keypoints.find((point) => point.name === 'right_shoulder');

  const SCORE_THRESHOLD = 0.3;
  const ANGLE_UP_THRESHOLD = 60; // Consider rep up when angle is less than this
  const ANGLE_DOWN_THRESHOLD = 150; // Consider rep down when angle is more than this

  // Check right arm
  if (rightWrist.score > SCORE_THRESHOLD &&
      rightElbow.score > SCORE_THRESHOLD &&
      rightShoulder.score > SCORE_THRESHOLD) {
    const rightAngle = calculateAngle(rightWrist, rightElbow, rightShoulder);
    rightAngleElement.textContent = `${Math.round(rightAngle)}°`;

    const repUp = rightAngle < ANGLE_UP_THRESHOLD;
    const repDown = rightAngle > ANGLE_DOWN_THRESHOLD;

    if (repUp && currentState.right === 'down') {
      console.info('Right arm rep up');
      currentState.right = 'up';
      repCount.right++;
      rightCountElement.textContent = repCount.right;
      rightCountElement.style.color = 'var(--primary-color)';
      triggerRepEffects();
    }
    if (repDown && currentState.right === 'up') {
      console.info('Right arm rep down');
      currentState.right = 'down';
      rightCountElement.style.color = 'var(--secondary-color)';
    }
  }

  // Check left arm
  if (leftWrist.score > SCORE_THRESHOLD &&
      leftElbow.score > SCORE_THRESHOLD &&
      leftShoulder.score > SCORE_THRESHOLD) {
    const leftAngle = calculateAngle(leftWrist, leftElbow, leftShoulder);
    leftAngleElement.textContent = `${Math.round(leftAngle)}°`;

    const repUp = leftAngle < ANGLE_UP_THRESHOLD;
    const repDown = leftAngle > ANGLE_DOWN_THRESHOLD;

    if (repUp && currentState.left === 'down') {
      console.info('Left arm rep up');
      currentState.left = 'up';
      repCount.left++;
      leftCountElement.textContent = repCount.left;
      leftCountElement.style.color = 'var(--primary-color)';
      triggerRepEffects();
    }
    if (repDown && currentState.left === 'up') {
      console.info('Left arm rep down');
      currentState.left = 'down';
      leftCountElement.style.color = 'var(--secondary-color)';
    }
  }
}

async function renderPrediction() {
  await checkGuiUpdate();

  if (!STATE.isModelChanged) {
    await renderResult();
  }

  rafId = requestAnimationFrame(renderPrediction);
};

async function init() {
  try {
    const loadingElement = document.getElementById('loading');
    
    // Show loading screen
    if (loadingElement) {
      loadingElement.style.display = 'flex';
    }

    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Setup GUI controls
    await setupDatGui(urlParams);

    // Setup performance stats
    stats = setupStats();
    
    // Initialize camera
    camera = await Camera.setup(STATE.camera);
    if (!camera) {
      throw new Error('Failed to initialize camera');
    }

    // Ensure backend is set before initialization
    if (!STATE.backend) {
      STATE.backend = 'tfjs-webgl'; // Set default backend if none is set
    }

    // Setup backend and initialize TensorFlow
    await setBackendAndEnvFlags(STATE.flags, STATE.backend);
    await tf.ready();
    
    // Create pose detector
    detector = await createDetector();
    if (!detector) {
      throw new Error('Failed to create detector');
    }

    // Setup canvas
    const canvas = document.getElementById('output');
    canvas.width = camera.video.width;
    canvas.height = camera.video.height;
    
    // Initialize renderer
    const isWebGPU = STATE.backend === 'tfjs-webgpu';
    useGpuRenderer = (urlParams.get('gpuRenderer') === 'true') && isWebGPU;
    renderer = useGpuRenderer ? 
      new RendererWebGPU(canvas) : 
      new RendererCanvas2d(canvas);

    // Hide loading screen when everything is ready
    if (loadingElement) {
      loadingElement.style.display = 'none';
    }

    // Start detection loop
    renderPrediction();

  } catch (error) {
    console.error('Error during initialization:', error);
    const errorElement = document.createElement('div');
    errorElement.className = 'error';
    errorElement.textContent = `Error: ${error.message}`;
    document.body.appendChild(errorElement);
    
    // Hide loading screen on error
    if (loadingElement) {
      loadingElement.style.display = 'none';
    }
  }
}

// Make sure we call init when the document is loaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

if (useGpuRenderer) {
  renderer.dispose();
}





