import { check } from "k6";
import { trackedRequest } from "../helpers.js";
import { BASE_URL, AUTH_HEADERS } from "../config.js";

export function testUserPermissions(ids) {
    const res = trackedRequest(
        "GET",
        `${BASE_URL}/v1/users/${ids.userId}/permissions`,
        null,
        AUTH_HEADERS,
        "user-permissions",
    );
    check(res, { "UserPermissions READ": (r) => r.status === 200 });
}
