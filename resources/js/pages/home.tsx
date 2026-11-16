import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDate } from '@/lib/dates';
import { create } from '@/routes/contests/registration';
import type { ContestStatus, ContestSummary } from '@/types';

type Props = {
    contests: ContestSummary[];
};

const unavailableLabels: Record<Exclude<ContestStatus, 'open'>, string> = {
    closed: 'Iscrizioni chiuse',
    sold_out: 'Posti esauriti',
};

export default function Home({ contests }: Props) {
    return (
        <>
            <Head title="Prossime gare" />

            <div className="min-h-svh bg-background p-6 md:p-10">
                <main className="mx-auto flex w-full max-w-3xl flex-col gap-6">
                    <header className="space-y-2">
                        <h1 className="text-2xl font-semibold">
                            Prossime gare
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Scegli una gara e iscriviti.
                        </p>
                    </header>

                    {contests.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Nessuna gara in programma.
                        </p>
                    ) : (
                        <ul className="flex flex-col gap-4">
                            {contests.map((contest) => (
                                <li key={contest.id}>
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>
                                                {contest.name}
                                            </CardTitle>
                                            <CardDescription>
                                                {formatDate(contest.date)}
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent className="flex items-center justify-between gap-4">
                                            <span className="text-sm">
                                                Posti rimasti:{' '}
                                                <strong>
                                                    {contest.places_left}
                                                </strong>
                                            </span>

                                            {contest.status === 'open' ? (
                                                <Button asChild>
                                                    <Link
                                                        href={create(
                                                            contest.id,
                                                        )}
                                                    >
                                                        Iscriviti
                                                    </Link>
                                                </Button>
                                            ) : (
                                                <Badge variant="secondary">
                                                    {
                                                        unavailableLabels[
                                                            contest.status
                                                        ]
                                                    }
                                                </Badge>
                                            )}
                                        </CardContent>
                                    </Card>
                                </li>
                            ))}
                        </ul>
                    )}
                </main>
            </div>
        </>
    );
}
