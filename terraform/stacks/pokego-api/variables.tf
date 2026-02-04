variable "project_id" {
  description = "GCPプロジェクトID"
  type        = string
}

variable "region" {
  description = "デプロイリージョン"
  type        = string
  default     = "asia-northeast1"
}

variable "service_name" {
  description = "サービス名"
  type        = string
  default     = "pokemon-go-calc"
}

variable "image" {
  description = "コンテナイメージURL"
  type        = string
}

variable "cloud_build_repository" {
  description = "Cloud Build リポジトリリソース名 (projects/PROJECT/locations/LOCATION/connections/CONNECTION/repositories/REPO)"
  type        = string
}
