module "pokego_api" {
  source = "../../stacks/pokego-api"

  project_id             = "sandbox-haruto-horinouchi"
  region                 = "asia-northeast1"
  image                  = "asia-northeast1-docker.pkg.dev/sandbox-haruto-horinouchi/pokemon-go-calc/app:latest"
  cloud_build_repository = "projects/sandbox-haruto-horinouchi/locations/asia-northeast1/connections/github/repositories/nanato12-pokemon-go-calc"
}
