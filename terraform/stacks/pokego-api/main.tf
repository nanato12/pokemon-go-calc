resource "google_project_service" "apis" {
  for_each = toset([
    "run.googleapis.com",
    "artifactregistry.googleapis.com",
    "cloudbuild.googleapis.com",
  ])

  service            = each.value
  disable_on_destroy = false
}

module "artifact_registry" {
  source = "../../modules/artifact_registry"

  location      = var.region
  repository_id = var.service_name
  description   = "Pokemon GO IV Extractor"

  depends_on = [google_project_service.apis]
}

module "cloud_run" {
  source = "../../modules/cloud_run"

  name                  = var.service_name
  location              = var.region
  image                 = var.image
  allow_unauthenticated = true

  depends_on = [google_project_service.apis]
}

module "cloud_build" {
  source = "../../modules/cloud_build"

  name       = "${var.service_name}-deploy"
  location   = var.region
  repository = var.cloud_build_repository

  depends_on = [google_project_service.apis]
}
