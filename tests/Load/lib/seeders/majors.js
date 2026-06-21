import { uid } from "../helpers.js";

export function generateMajorData(ids) {
    const uniqueId = uid();

    return {
        degree_id: ids.degreeId,
        faculty_id: ids.facultyId,
        code: `K6M_${uniqueId}`,
        name: `k6_major_${uniqueId}`,
    };
}
