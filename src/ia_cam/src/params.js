
import * as posedetection from '@tensorflow-models/pose-detection';
import {isiOS} from './util';

export const DEFAULT_LINE_WIDTH = 2;
export const DEFAULT_RADIUS = 4;

export const VIDEO_SIZE = {
  '640 X 480': {width: 640, height: 480},
  '640 X 360': {width: 640, height: 360},
  '360 X 270': {width: 360, height: 270},
};
export const STATE = {
  camera: {targetFPS: 60, deviceId: ''},
  backend: 'tfjs-webgl', // Set a default backend
  flags: {},
  modelConfig: {},
  model: posedetection.SupportedModels.MoveNet, // Use the actual model enum
  isModelChanged: false,
  isBackendChanged: false,
  isFlagChanged: false,
  isTargetFPSChanged: false,
};
export const BLAZEPOSE_CONFIG = {
  maxPoses: 1,
  type: 'full',
  scoreThreshold: 0.65,
  render3D: true,
};
export const POSENET_CONFIG = {
  maxPoses: 1,
  scoreThreshold: 0.5,
};
export const MOVENET_CONFIG = {
  maxPoses: 1,
  type: 'lightning',
  scoreThreshold: 0.3,
  customModel: '',
  enableTracking: false,
};
/**
 * This map describes tunable flags and theior corresponding types.
 *
 * The flags (keys) in the map satisfy the following two conditions:
 * - Is tunable. For example, `IS_BROWSER` and `IS_CHROME` is not tunable,
 * because they are fixed when running the scripts.
 * - Does not depend on other flags when registering in `ENV.registerFlag()`.
 * This rule aims to make the list streamlined, and, since there are
 * dependencies between flags, only modifying an independent flag without
 * modifying its dependents may cause inconsistency.
 * (`WEBGL_RENDER_FLOAT32_CAPABLE` is an exception, because only exposing
 * `WEBGL_FORCE_F16_TEXTURES` may confuse users.)
 */
export const TUNABLE_FLAG_VALUE_RANGE_MAP = {
  WEBGL_VERSION: [1, 2],
  WASM_HAS_SIMD_SUPPORT: [true, false],
  WASM_HAS_MULTITHREAD_SUPPORT: [true, false],
  WEBGL_CPU_FORWARD: [true, false],
  WEBGL_PACK: [true, false],
  WEBGL_FORCE_F16_TEXTURES: [true, false],
  WEBGL_RENDER_FLOAT32_CAPABLE: [true, false],
  WEBGL_FLUSH_THRESHOLD: [-1, 0, 0.25, 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2],
  CHECK_COMPUTATION_FOR_ERRORS: [true, false],
};

export const BACKEND_FLAGS_MAP = {
  ['tfjs-wasm']: ['WASM_HAS_SIMD_SUPPORT', 'WASM_HAS_MULTITHREAD_SUPPORT'],
  ['tfjs-webgl']: [
    'WEBGL_VERSION', 'WEBGL_CPU_FORWARD', 'WEBGL_PACK',
    'WEBGL_FORCE_F16_TEXTURES', 'WEBGL_RENDER_FLOAT32_CAPABLE',
    'WEBGL_FLUSH_THRESHOLD',
  ],
  ['tfjs-webgpu']: [],
  ['mediapipe-gpu']: [],
};

export const MODEL_BACKEND_MAP = {
  [posedetection.SupportedModels.PoseNet]: ['tfjs-webgl', 'tfjs-webgpu'],
  [posedetection.SupportedModels.MoveNet]: ['tfjs-webgl', 'tfjs-wasm', 'tfjs-webgpu'],
  [posedetection.SupportedModels.BlazePose]: ['mediapipe-gpu', 'tfjs-webgl', 'tfjs-webgpu'],
};

export const TUNABLE_FLAG_NAME_MAP = {
  PROD: 'production mode',
  WEBGL_VERSION: 'webgl version',
  WASM_HAS_SIMD_SUPPORT: 'wasm SIMD',
  WASM_HAS_MULTITHREAD_SUPPORT: 'wasm multithread',
  WEBGL_CPU_FORWARD: 'cpu forward',
  WEBGL_PACK: 'webgl pack',
  WEBGL_FORCE_F16_TEXTURES: 'enforce float16',
  WEBGL_RENDER_FLOAT32_CAPABLE: 'enable float32',
  WEBGL_FLUSH_THRESHOLD: 'GL flush wait time(ms)',
};


