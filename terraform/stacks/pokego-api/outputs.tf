output "service_url" {
  description = "Cloud RunサービスURL"
  value       = module.cloud_run.service_url
}

output "artifact_registry_url" {
  description = "Artifact RegistryリポジトリURL"
  value       = module.artifact_registry.repository_url
}

output "cloud_build_trigger_id" {
  description = "Cloud BuildトリガーID"
  value       = module.cloud_build.trigger_id
}
