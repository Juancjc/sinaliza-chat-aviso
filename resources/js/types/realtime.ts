export type RealtimeMessage = {
    id: number;
    mensagem: string;
    created_at: string;
    grupo: {
        id: number;
        nome: string;
    };
    user: {
        id: number;
        name: string;
        tipo_usuario: 'admin' | 'aluno';
        avatar_emoji: string;
    };
};
