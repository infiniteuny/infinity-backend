import { uid } from "../helpers.js";

export function generateDegreeData() {
    const uniqueId = uid();

    return {
        name: `k6_degree_${uniqueId}`,
        code: `K6D_${uniqueId}`,
    };
}
