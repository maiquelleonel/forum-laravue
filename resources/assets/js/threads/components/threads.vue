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
                    <tr v-for="dbthread in threads_res.data">
                        <td>{{ dbthread.id }}</td>
                        <td><a :href="'/threads/' + dbthread.id ">{{ dbthread.title }}</a></td>
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
        ],
        data() {
            return {
                threads_res: []
            }
        },
        mounted() {
            window.axios.get('/threads').then((res) => {
                this.threads_res = res.data
            })
        }
    }
</script>
