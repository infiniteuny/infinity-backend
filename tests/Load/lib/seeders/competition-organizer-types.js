import { uid } from "../helpers.js";

export function generateCompetitionOrganizerTypeData() {
    const uniqueId = uid();

    return {
        name: `k6_org_type_${uniqueId}`,
        weight: Math.floor(Math.random() * 10) + 1,
    };
}
