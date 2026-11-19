import { uid } from "../helpers.js";

export function generateCompetitionData() {
    const uniqueId = uid();

    return {
        name: `k6_competition_${uniqueId}`,
        shortname: `K6C_${uniqueId}`,
    };
}

export function generateCompetitionUpdateData() {
    const uniqueId = uid();

    return {
        name: `k6_competition_upd_${uniqueId}`,
    };
}
