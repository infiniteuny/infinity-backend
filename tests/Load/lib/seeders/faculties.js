import { uid } from "../helpers.js";

export function generateFacultyData() {
    const uniqueId = uid();

    return {
        name: `k6_faculty_${uniqueId}`,
        code: `K6F_${uniqueId}`,
        shortname: `KF_${uniqueId}`,
        description: `k6 faculty description ${uniqueId}`,
    };
}
