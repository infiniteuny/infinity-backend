import { check } from "k6";
import { trackedRequest } from "../helpers.js";
import { BASE_URL, AUTH_HEADERS } from "../config.js";

export function testCommunityGroups() {
    const res = trackedRequest(
        "GET",
        `${BASE_URL}/v1/community-groups`,
        null,
        AUTH_HEADERS,
        "community-groups",
    );
    check(res, { "CommunityGroups READ": (r) => r.status === 200 });
}
