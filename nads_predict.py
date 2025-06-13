import sys
import json
import joblib
import pandas as pd
import shap
from catboost import CatBoostClassifier
import datetime

log_file = "shap_predict_debug.log"

# Load model
try:
    model = joblib.load("catboost_nads_model.pkl")
except Exception as e:
    with open(log_file, "a") as f:
        f.write(f"{datetime.datetime.now()} - Error loading model: {str(e)}\n")
    print(json.dumps({"error": f"Model load failed: {str(e)}"}))
    sys.exit(1)

# Feature list
feature_names = [
    "duration", "protocol_type", "service", "flag", "src_bytes", "dst_bytes", "land",
    "wrong_fragment", "urgent", "hot", "num_failed_logins", "logged_in", "num_compromised",
    "root_shell", "su_attempted", "num_root", "num_file_creations", "num_shells",
    "num_access_files", "num_outbound_cmds", "is_host_login", "is_guest_login", "count",
    "srv_count", "serror_rate", "srv_serror_rate", "rerror_rate", "srv_rerror_rate",
    "same_srv_rate", "diff_srv_rate", "srv_diff_host_rate", "dst_host_count",
    "dst_host_srv_count", "dst_host_same_srv_rate", "dst_host_diff_srv_rate",
    "dst_host_same_src_port_rate", "dst_host_srv_diff_host_rate", "dst_host_serror_rate",
    "dst_host_srv_serror_rate", "dst_host_rerror_rate", "dst_host_srv_rerror_rate"
]

# Parse input JSON
if len(sys.argv) <= 1:
    print(json.dumps({"error": "Missing input JSON from command line"}))
    sys.exit(1)

try:
    input_json = json.loads(sys.argv[1])
except Exception as e:
    print(json.dumps({"error": f"Invalid input: {str(e)}"}))
    sys.exit(1)

missing = [f for f in feature_names if f not in input_json]
if missing:
    print(json.dumps({"error": f"Missing feature(s): {', '.join(missing)}"}))
    sys.exit(1)

try:
    input_data = [input_json[f] for f in feature_names]
    X = pd.DataFrame([input_data], columns=feature_names)

    pred = int(model.predict(X)[0])
    explainer = shap.Explainer(model)
    shap_values = explainer(X)
    shap_scores = list(zip(feature_names, shap_values.values[0]))
    shap_scores.sort(key=lambda x: abs(x[1]), reverse=True)
    top_features = [name for name, val in shap_scores[:3]]

    output = {
        "prediction": pred,
        "explanation": top_features
    }

    print(json.dumps(output))

except Exception as e:
    print(json.dumps({"error": f"Prediction failure: {str(e)}"}))
    sys.exit(1)
