import { check } from "k6";
import { trackedRequest } from "../helpers.js";
import { BASE_URL, AUTH_HEADERS } from "../config.js";

export function testUsers(ids) {
    const res = trackedRequest(
        "GET",
        `${BASE_URL}/v1/users?includes=major,major.faculty`,
        null,
        AUTH_HEADERS,
        "users",
    );
    check(res, { "Users READ": (r) => r.status === 200 });
}
