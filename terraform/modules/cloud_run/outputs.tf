output "service_url" {
  description = "Cloud RunサービスURL"
  value       = google_cloud_run_v2_service.this.uri
}

output "service_name" {
  description = "Cloud Runサービス名"
  value       = google_cloud_run_v2_service.this.name
}
