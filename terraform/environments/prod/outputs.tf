output "service_url" {
  description = "Cloud RunサービスURL"
  value       = module.pokego_api.service_url
}

output "artifact_registry_url" {
  description = "Artifact RegistryリポジトリURL"
  value       = module.pokego_api.artifact_registry_url
}
