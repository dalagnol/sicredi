# Sicredi

Validação técnica de conhecimentos para Sicredi Pioneira.

## Levantando o projeto para desenvolvimento
### Requisitos

- PHP v7.4

- Docker v4

O teste utiliza o template inicial dado pelo Laravel atarvés do comando:

```
curl -s "https://laravel.build/example-app" | bash
```

E para iniciar a aplicação:

```
./vendor/bin/sail up
```

[Mais instruções sobre execução.](https://laravel.com/docs/8.x/installation)

## Objetivo

A tabela requisitada pelo teste pode ser obtida através da URL abaixo (o processo levará alguns minutos para terminar):

http://localhost/export

Após execução, os logs do processo podem ser encontrados no caminho:

```
/sicredi/public/logs.txt
```
