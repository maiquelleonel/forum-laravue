<template>
    <div id="preloader" v-show="counter">
        <div class="image">
            <img src="/img/preloader.gif" alt="" />
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                counter: 0
            }
        },
        mounted() {
            window.axios.interceptors.request.use((req) => {
                this.counter++
                return req
            }, (err) => {
                return Promise.reject(err)
            })

            window.axios.interceptors.response.use((res) => {
                this.counter--
                return res
            }, (err) => {
                return Promise.reject(err)
            })
        }
    }
</script>

<style>
#preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.66);
}
#preloader .image {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-49%, -50%);
    width: 200px;
    height: 200px;
    box-shadow: 0 0 1pc -5px lightcoral;
    border-radius: 100%;
    background: #fff;
    overflow: hidden;
}
#preloader .image img {
    position: relative;
    top: 0;
    left: -22px;

}
</style>
