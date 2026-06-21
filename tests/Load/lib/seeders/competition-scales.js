import { uid } from "../helpers.js";

export function generateCompetitionScaleData() {
    const uniqueId = uid();

    return {
        name: `k6_scale_${uniqueId}`,
        weight: Math.floor(Math.random() * 10) + 1,
    };
}
