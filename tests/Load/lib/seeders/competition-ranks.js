import { uid } from "../helpers.js";

export function generateCompetitionRankData() {
    const uniqueId = uid();

    return {
        name: `k6_rank_${uniqueId}`,
        weight: Math.floor(Math.random() * 10) + 1,
    };
}
