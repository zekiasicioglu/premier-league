export interface Team {
    id: number
    name?: string
    city?: string
    strength?: number
    prediction?: number
    played?: number
    won?: number
    drawn?: number
    lost?: number
    goals_for?: number
    goals_against?: number
    goals_difference?: number
    points?: number
}

export interface TeamSelectData {
    id: number
    name: string
    city: string
    strength?: number
}

export interface FixtureItem {
    home_team_id: number
    home_team_name: string
    home_team_score?: number
    away_team_id: number
    away_team_name: string
    away_team_score?: number
}

export interface GeneratedFixtureData {
    week: number,
    matches: FixtureItem[]
}

export interface CurrentWeekData {
    week: number,
    is_played: boolean,
    fixture: FixtureItem[]
    teams: Team[]
}
