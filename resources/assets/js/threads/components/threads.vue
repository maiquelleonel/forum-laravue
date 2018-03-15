<template>
    <div class="card">
        <div class="card-content">
            <span class="card-title">{{ title }}</span>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ thread }}</th>
                        <th>{{ replies }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><a href="/threads/1">Legal esse tópico. Bem loco!</a></td>
                        <td>32</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        props: [
            'title','thread','replies'
        ]
    }
</script>
