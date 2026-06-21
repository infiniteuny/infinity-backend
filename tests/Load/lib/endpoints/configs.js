import { check } from "k6";
import { trackedRequest } from "../helpers.js";
import { BASE_URL, AUTH_HEADERS } from "../config.js";

export function testConfigs() {
    const res = trackedRequest(
        "GET",
        `${BASE_URL}/v1/configs`,
        null,
        AUTH_HEADERS,
        "configs",
    );
    check(res, { "Configs READ": (r) => r.status === 200 });
}
