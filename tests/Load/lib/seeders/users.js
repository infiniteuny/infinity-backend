import { uid } from "../helpers.js";

export function generateUserData(ids) {
    const uniqueId = uid();

    return {
        name: `k6_user_${uniqueId}`,
        username: `k6user_${uniqueId}`,
        email_address: `k6user_${uniqueId}@example.com`,
        phone_number: `+628${uniqueId}`,
        student_id: `K6${uniqueId}`,
        major_id: ids.majorId,
        is_member: true,
        is_extraordinary: false,
        start_date: "2025-01-01",
        end_date: "2026-12-31",
    };
}

export function generateUserUpdateData(ids) {
    const uniqueId = uid();

    return {
        name: `k6_user_upd_${uniqueId}`,
        username: `k6userupd_${uniqueId}`,
        email_address: `k6userupd_${uniqueId}@example.com`,
        phone_number: `+628${uniqueId}`,
        student_id: `K6U${uniqueId}`,
    };
}
