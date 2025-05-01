from flask import Flask, request, jsonify
from flask_cors import CORS
import openai

app = Flask(__name__)
CORS(app)

# OpenAI API Key
openai.api_key = "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"

# Assistant ID (Already set up on OpenAI platform)
ASSISTANT_ID1 = "asst_osqXQt3wuinfSILs1eqZK4DN"
ASSISTANT_ID2 = "asst_Dl3HtSkhwhzlFNY2MJUbejbp"


# Store thread ID globally
thread_id = None

def get_assistant_response(user_input):
    """Send user input to OpenAI assistant and return response."""
    global thread_id

    try:
        # Create a new thread only if it doesn't exist
        if thread_id is None:
            thread = openai.beta.threads.create()
            thread_id = thread.id

        # Send user message
        openai.beta.threads.messages.create(
            thread_id=thread_id, role="user", content=user_input
        )

        # Create a run
        run = openai.beta.threads.runs.create(
            thread_id=thread_id, assistant_id=ASSISTANT_ID1
        )

        # Wait for completion
        while True:
            run_status = openai.beta.threads.runs.retrieve(
                thread_id=thread_id, run_id=run.id
            )

            if run_status.status == "completed":
                messages = openai.beta.threads.messages.list(thread_id)
                assistant_message = next((msg for msg in messages.data if msg.role == "assistant"), None)
                return assistant_message.content[0].text.value if assistant_message else "No response"
            elif run_status.status not in ["queued", "in_progress"]:
                return "Error: Run failed"

    except Exception as e:
        return f"Error: {e}"

@app.route("/chat", methods=["POST"])
def chat():
    """Chat endpoint: receives user text, gets AI response."""
    data = request.get_json()
    user_input = data.get("text", "")

    if not user_input:
        return jsonify({"error": "No input text provided"}), 400

    # Get AI response
    assistant_response = get_assistant_response(user_input)

    if "Error" in assistant_response:
        return jsonify({"error": assistant_response}), 500

    return jsonify({"response": assistant_response})


def get_assistant_response_web(user_input):
    """Send user input to OpenAI assistant and return response."""
    global thread_id

    try:
        # Create a new thread only if it doesn't exist
        if thread_id is None:
            thread = openai.beta.threads.create()
            thread_id = thread.id

        # Send user message
        openai.beta.threads.messages.create(
            thread_id=thread_id, role="user", content=user_input
        )

        # Create a run
        run = openai.beta.threads.runs.create(
            thread_id=thread_id, assistant_id=ASSISTANT_ID2
        )

        # Wait for completion
        while True:
            run_status = openai.beta.threads.runs.retrieve(
                thread_id=thread_id, run_id=run.id
            )

            if run_status.status == "completed":
                messages = openai.beta.threads.messages.list(thread_id)
                assistant_message = next((msg for msg in messages.data if msg.role == "assistant"), None)
                return assistant_message.content[0].text.value if assistant_message else "No response"
            elif run_status.status not in ["queued", "in_progress"]:
                return "Error: Run failed"

    except Exception as e:
        return f"Error: {e}"

@app.route("/chat_web", methods=["POST"])
def chat_web():
    """Chat endpoint: receives user text, gets AI response."""
    data = request.get_json()
    user_input = data.get("text", "")

    if not user_input:
        return jsonify({"error": "No input text provided"}), 400

    # Get AI response
    assistant_response = get_assistant_response_web(user_input)

    if "Error" in assistant_response:
        return jsonify({"error": assistant_response}), 500

    return jsonify({"response": assistant_response})


if __name__ == "__main__":
    app.run(debug=True)
