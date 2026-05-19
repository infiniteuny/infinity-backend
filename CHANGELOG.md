# [1.12.0](https://github.com/infiniteuny/infinity-backend/compare/v1.11.18...v1.12.0) (2026-05-19)


### Bug Fixes

* **controller:** Also prevent dispatching DeleteBlob for null animation manifests on CommunityGroupAdminMember ([d9fbaff](https://github.com/infiniteuny/infinity-backend/commit/d9fbaff79b3aae8e9340c670e16c89ed260dc866))
* **controller:** Prevent dispatching DeleteBlob for null animation manifests ([a4ea892](https://github.com/infiniteuny/infinity-backend/commit/a4ea8922a454d968ecca4c9cbf6b20c0a900a5c0))
* **seeder:** Move seeder for entity permission with public read to other permissions ([d4413da](https://github.com/infiniteuny/infinity-backend/commit/d4413da0e7e2732945b779583003730ad0d7f472))


### Features

* **workflow:** Add changelog generation step to release workflow ([7b58908](https://github.com/infiniteuny/infinity-backend/commit/7b589084de61212d03888d35ea5acb26f7b1c85f))



## [1.11.18](https://github.com/infiniteuny/infinity-backend/compare/v1.11.17...v1.11.18) (2026-05-18)


### Bug Fixes

* **data:** Correct SsoGroupData user IDs string int convertion function ([9397033](https://github.com/infiniteuny/infinity-backend/commit/9397033f8d74405af03a093ae1e723d210459318))
* **job:** Correct sync job name ([aef5180](https://github.com/infiniteuny/infinity-backend/commit/aef5180f3efe333d652526f30ddb507746d7d94f))
* **job:** Fix file name typo ([75b3408](https://github.com/infiniteuny/infinity-backend/commit/75b3408fd70adbcc291d22e7954e4be182fb999e))



## [1.11.17](https://github.com/infiniteuny/infinity-backend/compare/v1.11.16...v1.11.17) (2026-05-18)


### Features

* **job:** Add job to sync active and inactive member to group and SSO ([c1cdd0e](https://github.com/infiniteuny/infinity-backend/commit/c1cdd0ea9a9eb1193f01234c86a668f9bcf87922))
* **job:** Add job to sync member and admin group to SSO ([16e4484](https://github.com/infiniteuny/infinity-backend/commit/16e44847f42db7f5fbd7f60de5b347f65e935784))



## [1.11.16](https://github.com/infiniteuny/infinity-backend/compare/v1.11.15...v1.11.16) (2026-05-17)


### Bug Fixes

* **job:** Replace Optional::create() with default values for 'parents' and 'is_superuser' in SsoGroupData ([5836bad](https://github.com/infiniteuny/infinity-backend/commit/5836bad80f1091993db01f2cfbac2dabccdb63b4))



## [1.11.15](https://github.com/infiniteuny/infinity-backend/compare/v1.11.14...v1.11.15) (2026-05-17)


### Bug Fixes

* **job:** Rename 'sso_parent_ids' to 'parents' for additional SsoGroupData ([6d56690](https://github.com/infiniteuny/infinity-backend/commit/6d56690cb85ed5403639acec88ccaf47125b06b8))



