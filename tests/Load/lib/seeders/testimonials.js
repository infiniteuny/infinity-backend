import http from "k6/http";
import { uid, IMAGE_BIN } from "../helpers.js";

export function generateTestimonialData() {
    const uniqueId = uid();

    return {
        name: `k6_testimonial_${uniqueId}`,
        position: `k6_position_${uniqueId}`,
        photo: http.file(
            IMAGE_BIN,
            `testimonial_${uniqueId}.jpg`,
            "image/jpeg",
        ),
        content: `k6 testimonial content ${uniqueId}`,
    };
}

export function generateTestimonialUpdateData() {
    const uniqueId = uid();

    return {
        name: `k6_testimonial_upd_${uniqueId}`,
        content: `k6 testimonial content updated ${uniqueId}`,
    };
}
