const italianDate = new Intl.DateTimeFormat('it-IT', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

/** Format a `YYYY-MM-DD` date for Italian readers without shifting it across time zones. */
export function formatDate(date: string): string {
    const [year, month, day] = date.split('-').map(Number);

    return italianDate.format(new Date(year, month - 1, day));
}
