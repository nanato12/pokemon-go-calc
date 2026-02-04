output "trigger_id" {
  description = "Cloud BuildトリガーID"
  value       = google_cloudbuild_trigger.this.trigger_id
}
