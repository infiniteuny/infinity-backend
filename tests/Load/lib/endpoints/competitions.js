import { check } from "k6";
import { trackedRequest } from "../helpers.js";
import { BASE_URL, AUTH_HEADERS } from "../config.js";

export function testCompetitions() {
    const res = trackedRequest(
        "GET",
        `${BASE_URL}/v1/competitions`,
        null,
        AUTH_HEADERS,
        "competitions",
    );
    check(res, { "Competitions READ": (r) => r.status === 200 });
}
