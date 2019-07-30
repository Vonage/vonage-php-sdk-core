workflow "New release" {
  on = "release"
  resolves = ["Add Changelog"]
}

action "Add Changelog" {
  uses = "nexmo/github-actions/nexmo-changelog@master"
  secrets = ["CHANGELOG_AUTH_TOKEN"]
  env = {
    CHANGELOG_CATEGORY = "Server SDK"
    CHANGELOG_SUBCATEGORY = "php"
    CHANGELOG_RELEASE_TITLE = "nexmo-php"
  }
}
