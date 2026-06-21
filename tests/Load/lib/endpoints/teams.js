import { check } from "k6";
import { trackedRequest, extractId } from "../helpers.js";
import { BASE_URL, JSON_HEADERS, AUTH_HEADERS } from "../config.js";
import { generateTeamData } from "../seeders/teams.js";

export function testTeams(ids) {
    let res, resourceId;

    res = trackedRequest(
        "POST",
        `${BASE_URL}/v1/teams`,
        JSON.stringify(generateTeamData(ids)),
        JSON_HEADERS,
        "teams",
    );
    check(res, { "Teams CREATE": (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest(
            "DELETE",
            `${BASE_URL}/v1/teams/${resourceId}`,
            null,
            AUTH_HEADERS,
            "teams",
        );
        check(res, { "Teams DELETE": (r) => r.status === 200 });
    }
}
