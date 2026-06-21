import { uid } from "../helpers.js";

export function generateCompetitionTimeRangeData() {
    const uniqueId = uid();

    return {
        name: `k6_time_range_${uniqueId}`,
        weight: Math.floor(Math.random() * 10) + 1,
    };
}
