import { Form, Head } from '@inertiajs/react';
import AlertError from '@/components/alert-error';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDate } from '@/lib/dates';
import { home } from '@/routes';
import { store } from '@/routes/contests/registration';
import type { ContestHeading } from '@/types';

type Props = {
    contest: ContestHeading;
};

export default function Registration({ contest }: Props) {
    return (
        <>
            <Head title={`Iscrizione a ${contest.name}`} />

            <Form {...store.form(contest.id)} className="flex flex-col gap-6">
                {({ processing, errors }) => (
                    <>
                        <div className="text-center">
                            <p className="font-medium">{contest.name}</p>
                            <p className="text-sm text-muted-foreground">
                                {formatDate(contest.date)}
                            </p>
                        </div>

                        {errors.contest && (
                            <AlertError
                                title="Iscrizione non disponibile"
                                errors={[errors.contest]}
                            />
                        )}

                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="first_name">Nome</Label>
                                <Input
                                    id="first_name"
                                    name="first_name"
                                    required
                                    autoFocus
                                    autoComplete="given-name"
                                />
                                <InputError message={errors.first_name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="last_name">Cognome</Label>
                                <Input
                                    id="last_name"
                                    name="last_name"
                                    required
                                    autoComplete="family-name"
                                />
                                <InputError message={errors.last_name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="birth_date">
                                    Data di nascita
                                </Label>
                                <Input
                                    id="birth_date"
                                    type="date"
                                    name="birth_date"
                                    required
                                    autoComplete="bday"
                                />
                                <InputError message={errors.birth_date} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="text"
                                    inputMode="email"
                                    name="email"
                                    required
                                    autoComplete="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full"
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                Iscriviti
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            <TextLink href={home()}>
                                Torna all'elenco delle gare
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Registration.layout = {
    title: 'Iscrizione alla gara',
    description: 'Inserisci i tuoi dati per iscriverti',
};
