import { uid } from "../helpers.js";

export function generateCompetitionOutputData() {
    const uniqueId = uid();

    return {
        name: `k6_output_${uniqueId}`,
        weight: Math.floor(Math.random() * 10) + 1,
    };
}
