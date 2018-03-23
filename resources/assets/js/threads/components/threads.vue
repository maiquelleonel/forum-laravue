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
        <div class="card-content">
            <span class="card-title">{{ newThread }}</span>
            <form @submit.prevent="save">
                <div class="input-field">
                    <input type="text" :placeholder="threadTitle" v-model="new_thread.title"/>
                </div>
                <div class="input-field">
                    <textarea class="materialize-textarea" :placeholder="threadBody"
                              v-model="new_thread.body"></textarea>
                </div>
                <button type="submit" class="btn red accent-2">{{ send }}</button>
            </form>

        </div>
    </div>
</template>

<script>
    export default {
        props: [
            'title','thread','replies','newThread','threadTitle','threadBody','send'
        ],
        data() {
            return {
                threads_res: [],
                new_thread: {
                    title:'',
                    body:''
                }
            }
        },
        methods: {
            save(){
                window.axios.post('/threads', this.new_thread ).then((res) => {
                    this.getThreads()
                })
            },
            getThreads() {
                window.axios.get('/threads').then((res) => {
                    this.threads_res = res.data
                })
            }
        },
        mounted() {
            this.getThreads()
        }
    }
</script>
