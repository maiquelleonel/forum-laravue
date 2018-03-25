
window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');

    require('materialize-css/dist/js/materialize')
    require('./parallax-header')

} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key:         '7bf6535334edbe1e7df4',
    cluster:     'mt1',
    encrypted:   true
});

import swal from 'sweetalert2'

const successCallback = (res) => {
    return res;
}

const errorCallback = (err) => {
    console.log(err)
    if (err.response.status == 401) {
        swal({
            title:             'Autenticação',
            text:              'Para acessar esse recurso você precisa estar autenticado. Você será redirecionado.',
            type:              'warning',
            showCancelButton:  true,
            confirmButtonText: 'Ok!',
            cancelButtonText:  'Não, obrigado!'
        }).then( (res) => {
            if ( res.value ) {
                window.location = '/login'
            }
        })
    } else {
        swal({
            title:             'Erro!',
            text:              'Oops! Algo deu errrado. Sorry :(',
            type:              'error',
            showCancelButton:  false,
            confirmButtonText: 'Ok!'
        })
    }
    return Promise.reject(err)
}

window.axios.interceptors.response.use(successCallback, errorCallback)

window.Vue = require('vue')
Vue.component('loader', require('./commons/axiosLoader.vue'))

const commonApps = new Vue({
    el: '#loader'
})
