import { uid } from "../helpers.js";

export function generateCompetitionTeamTypeData() {
    const uniqueId = uid();

    return {
        name: `k6_team_type_${uniqueId}`,
        weight: Math.floor(Math.random() * 10) + 1,
    };
}
