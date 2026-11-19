import { uid } from "../helpers.js";

export function generateTeamData(ids) {
    const uniqueId = uid();

    return {
        leader_id: ids.userId,
        team_type_id: ids.competitionTeamTypeId,
        name: `k6_team_${uniqueId}`,
    };
}

export function generateTeamUpdateData(ids) {
    const uniqueId = uid();

    return {
        leader_id: ids.userId,
        team_type_id: ids.competitionTeamTypeId,
        name: `k6_team_upd_${uniqueId}`,
    };
}
