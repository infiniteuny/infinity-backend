import { check } from "k6";
import { trackedRequest } from "../helpers.js";
import { BASE_URL, AUTH_HEADERS } from "../config.js";

export function testTestimonials() {
    const res = trackedRequest(
        "GET",
        `${BASE_URL}/v1/testimonials`,
        null,
        AUTH_HEADERS,
        "testimonials",
    );
    check(res, { "Testimonials READ": (r) => r.status === 200 });
}
