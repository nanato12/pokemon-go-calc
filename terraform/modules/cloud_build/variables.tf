variable "name" {
  description = "トリガー名"
  type        = string
}

variable "location" {
  description = "トリガーのロケーション"
  type        = string
}

variable "repository" {
  description = "Cloud Build リポジトリリソース名 (projects/PROJECT/locations/LOCATION/connections/CONNECTION/repositories/REPO)"
  type        = string
}

variable "branch" {
  description = "トリガー対象ブランチ（正規表現）"
  type        = string
  default     = "^main$"
}

variable "cloudbuild_yaml" {
  description = "cloudbuild.yamlのパス"
  type        = string
  default     = "cloudbuild.yaml"
}

variable "substitutions" {
  description = "ビルド変数の上書き"
  type        = map(string)
  default     = {}
}
