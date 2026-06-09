<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Bell,
    MessageCircle,
    Pencil,
    Plus,
    Trash2,
    UserRoundPlus,
    Users,
} from '@lucide/vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import { computed } from 'vue';

type Grupo = {
    id: number;
    nome: string;
    descricao: string | null;
    admin: { id: number; name: string };
    participantes_count: number;
    mensagens_count: number;
};

defineProps<{ grupos: Grupo[] }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value.tipo_usuario === 'admin');

const excluirGrupo = (grupo: Grupo) => {
    if (window.confirm(`Excluir o grupo "${grupo.nome}"?`)) {
        router.delete(`/grupos/${grupo.id}`);
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <main
        class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-8 p-4 md:p-8"
    >
        <section
            class="overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 via-blue-600 to-cyan-500 px-6 py-8 text-white shadow-xl shadow-blue-950/10 md:px-10"
        >
            <div
                class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <Tag
                        :value="isAdmin ? 'Administrador' : 'Aluno'"
                        severity="contrast"
                        class="mb-4"
                    />
                    <h1 class="text-3xl font-bold tracking-tight md:text-4xl">
                        Olá, {{ user.name }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-blue-50">
                        {{
                            isAdmin
                                ? 'Crie grupos, reúna alunos e mantenha todos informados.'
                                : 'Acompanhe seus grupos e converse com sua turma.'
                        }}
                    </p>
                </div>
                <Button
                    v-if="isAdmin"
                    label="Criar novo grupo"
                    icon="pi pi-plus"
                    severity="contrast"
                    @click="router.visit('/grupos/create')"
                >
                    <template #icon><Plus class="size-4" /></template>
                </Button>
            </div>
        </section>

        <section>
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight">
                        {{
                            isAdmin
                                ? 'Seus grupos'
                                : 'Grupos em que você participa'
                        }}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ grupos.length }} grupo(s) disponível(is)
                    </p>
                </div>
            </div>

            <div
                v-if="grupos.length"
                class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
            >
                <Card
                    v-for="grupo in grupos"
                    :key="grupo.id"
                    class="overflow-hidden border border-border shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <template #title>
                        <div class="flex items-start justify-between gap-3">
                            <span class="line-clamp-1">{{ grupo.nome }}</span>
                            <MessageCircle
                                class="size-5 shrink-0 text-blue-600"
                            />
                        </div>
                    </template>
                    <template #subtitle>
                        <span v-if="!isAdmin"
                            >Criado por {{ grupo.admin.name }}</span
                        >
                        <span v-else>Gerenciado por você</span>
                    </template>
                    <template #content>
                        <p
                            class="mb-5 line-clamp-3 min-h-12 text-sm text-muted-foreground"
                        >
                            {{ grupo.descricao || 'Grupo sem descrição.' }}
                        </p>
                        <div class="flex gap-4 text-sm text-muted-foreground">
                            <span class="flex items-center gap-1.5">
                                <Users class="size-4" />
                                {{ grupo.participantes_count }} alunos
                            </span>
                            <span class="flex items-center gap-1.5">
                                <MessageCircle class="size-4" />
                                {{ grupo.mensagens_count }}
                            </span>
                        </div>
                    </template>
                    <template #footer>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                label="Abrir chat"
                                size="small"
                                @click="
                                    router.visit(`/grupos/${grupo.id}/chat`)
                                "
                            >
                                <template #icon
                                    ><MessageCircle class="size-4"
                                /></template>
                            </Button>
                            <template v-if="isAdmin">
                                <Button
                                    v-tooltip.top="'Participantes'"
                                    severity="secondary"
                                    size="small"
                                    rounded
                                    @click="
                                        router.visit(
                                            `/grupos/${grupo.id}/participantes`,
                                        )
                                    "
                                >
                                    <template #icon
                                        ><UserRoundPlus class="size-4"
                                    /></template>
                                </Button>
                                <Button
                                    v-tooltip.top="'Enviar aviso'"
                                    severity="secondary"
                                    size="small"
                                    rounded
                                    @click="
                                        router.visit(
                                            `/grupos/${grupo.id}/avisos/create`,
                                        )
                                    "
                                >
                                    <template #icon
                                        ><Bell class="size-4"
                                    /></template>
                                </Button>
                                <Button
                                    v-tooltip.top="'Editar'"
                                    severity="secondary"
                                    size="small"
                                    rounded
                                    @click="
                                        router.visit(`/grupos/${grupo.id}/edit`)
                                    "
                                >
                                    <template #icon
                                        ><Pencil class="size-4"
                                    /></template>
                                </Button>
                                <Button
                                    v-tooltip.top="'Excluir'"
                                    severity="danger"
                                    size="small"
                                    rounded
                                    @click="excluirGrupo(grupo)"
                                >
                                    <template #icon
                                        ><Trash2 class="size-4"
                                    /></template>
                                </Button>
                            </template>
                        </div>
                    </template>
                </Card>
            </div>

            <div
                v-else
                class="rounded-3xl border border-dashed border-border bg-muted/30 px-6 py-16 text-center"
            >
                <MessageCircle class="mx-auto size-10 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-semibold">
                    Nenhum grupo por aqui
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        isAdmin
                            ? 'Crie seu primeiro grupo para começar.'
                            : 'Você ainda não foi adicionado a um grupo.'
                    }}
                </p>
            </div>
        </section>
    </main>
</template>
