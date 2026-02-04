resource "google_cloudbuild_trigger" "this" {
  name     = var.name
  location = var.location

  repository_event_config {
    repository = var.repository
    push {
      tag = var.tag
    }
  }

  filename = var.cloudbuild_yaml

  substitutions = var.substitutions
}
