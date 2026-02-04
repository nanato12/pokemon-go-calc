variable "name" {
  description = "Cloud Runサービス名"
  type        = string
}

variable "location" {
  description = "デプロイリージョン"
  type        = string
}

variable "image" {
  description = "コンテナイメージURL"
  type        = string
}

variable "container_port" {
  description = "コンテナポート"
  type        = number
  default     = 8080
}

variable "cpu" {
  description = "CPU割り当て"
  type        = string
  default     = "1"
}

variable "memory" {
  description = "メモリ割り当て"
  type        = string
  default     = "512Mi"
}

variable "min_instance_count" {
  description = "最小インスタンス数"
  type        = number
  default     = 0
}

variable "max_instance_count" {
  description = "最大インスタンス数"
  type        = number
  default     = 1
}

variable "allow_unauthenticated" {
  description = "認証なしアクセスを許可するか"
  type        = bool
  default     = false
}
