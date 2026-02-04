resource "google_cloudbuild_trigger" "this" {
  name     = var.name
  location = var.location

  repository_event_config {
    repository = var.repository
    push {
      branch = var.branch
    }
  }

  filename = var.cloudbuild_yaml

  substitutions = var.substitutions
}
