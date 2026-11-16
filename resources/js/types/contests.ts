export type ContestStatus = 'open' | 'closed' | 'sold_out';

export type ContestSummary = {
    id: number;
    name: string;
    date: string;
    places_left: number;
    status: ContestStatus;
};

export type ContestHeading = Pick<ContestSummary, 'id' | 'name' | 'date'>;
