## [1.12.4](https://github.com/infiniteuny/infinity-backend/compare/v1.12.3...v1.12.4) (2026-06-16)


### Bug Fixes

* **controller:** Fix core team and CGAdmin deletetion order ([006beac](https://github.com/infiniteuny/infinity-backend/commit/006beac0915bd9122efeb6bea0ab15a082074820))
* **model:** Add missing is_active to community group fillable ([f2abd64](https://github.com/infiniteuny/infinity-backend/commit/f2abd647841a11ca594f38c171678f99e4d7977b))



## [1.12.3](https://github.com/infiniteuny/infinity-backend/compare/v1.12.2...v1.12.3) (2026-06-01)


### Bug Fixes

* **controller:** Change permission checking on extend user membership to check for manage-user-membership ([b74cfd7](https://github.com/infiniteuny/infinity-backend/commit/b74cfd7719ddccf385ea2a4c9d0a12b0fea681d5))



## [1.12.2](https://github.com/infiniteuny/infinity-backend/compare/v1.12.1...v1.12.2) (2026-05-31)


### Bug Fixes

* **controller:** Add authorization check for extending other user membership ([730b506](https://github.com/infiniteuny/infinity-backend/commit/730b5064530a1cf87026e2bff9a5cd6e0746bed7))
* **controller:** Fix ambigous id filter ([792b59f](https://github.com/infiniteuny/infinity-backend/commit/792b59f2030f18f51bc932c28f263968f9788848))
* **deps:** update non-major dependencies ([4bb0924](https://github.com/infiniteuny/infinity-backend/commit/4bb0924f552b8c61da07f074e39ca2c44359aeed))



## [1.12.1](https://github.com/infiniteuny/infinity-backend/compare/v1.12.0...v1.12.1) (2026-05-19)


### Bug Fixes

* **controller:** Correct blob manifest retrieval on destroy method in CommunityGroupAdminMemberController and CoreTeamMemberController ([8742965](https://github.com/infiniteuny/infinity-backend/commit/8742965d6143a202346401a57bd42752615e1f15))
* **controller:** Correct user ID retrieval on leader checking  in TeamMemberController ([0cc160d](https://github.com/infiniteuny/infinity-backend/commit/0cc160ddbc4f1253d4c1603ec6d9d10bb1143945))



# [1.12.0](https://github.com/infiniteuny/infinity-backend/compare/v1.11.18...v1.12.0) (2026-05-19)


### Bug Fixes

* **controller:** Also prevent dispatching DeleteBlob for null animation manifests on CommunityGroupAdminMember ([d9fbaff](https://github.com/infiniteuny/infinity-backend/commit/d9fbaff79b3aae8e9340c670e16c89ed260dc866))
* **controller:** Prevent dispatching DeleteBlob for null animation manifests ([a4ea892](https://github.com/infiniteuny/infinity-backend/commit/a4ea8922a454d968ecca4c9cbf6b20c0a900a5c0))
* **seeder:** Move seeder for entity permission with public read to other permissions ([d4413da](https://github.com/infiniteuny/infinity-backend/commit/d4413da0e7e2732945b779583003730ad0d7f472))


### Features

* **workflow:** Add changelog generation step to release workflow ([7b58908](https://github.com/infiniteuny/infinity-backend/commit/7b589084de61212d03888d35ea5acb26f7b1c85f))



