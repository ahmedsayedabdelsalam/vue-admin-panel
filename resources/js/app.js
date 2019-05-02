/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

// gates
import Gate from './Gate';
Vue.prototype.$gate = new Gate(window.user)

// vue router
import VueRouter from 'vue-router';
Vue.use(VueRouter);
const routes = [
    { path: '/dashboard', component: require('./components/Dashboard').default },
    { path: '/users', component: require('./components/Users').default },
    { path: '/profile', component: require('./components/Profile').default },
    { path: '/developer', component: require('./components/Developer').default },
    { path: '*', component: require('./components/NotFound').default }
];
const router = new VueRouter({
    mode: 'history',
    routes // short for `routes: routes`
});

// uppercase first letter filter
Vue.filter('upText', function(text) {
    return text.charAt(0).toUpperCase() + text.slice(1)
});

// vue form handler
import { Form, HasError, AlertError } from 'vform';
window.Form = Form;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);

// moment date filter
import moment from 'moment';
Vue.filter('myDate', function(date) {
    return moment(date).format('MMMM Do YYYY');
});

// sweet alert
import Swal from 'sweetalert2';
window.Swal = Swal;
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});
window.Toast = Toast;

// vue progress bar
import VueProgressBar from 'vue-progressbar';
Vue.use(VueProgressBar, {
    color: 'rgb(143, 255, 199)',
    failedColor: 'red',
    height: '2px'
});

// event instance
window.VueEvent = new Vue();

// passport components
Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);

// 404 component
Vue.component(
    'NotFound',
    require('./components/NotFound.vue').default
);

// pagination component
Vue.component(
    'pagination',
    require('laravel-vue-pagination')
);



/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router,
    data: {
        searchField: ''
    },
    methods: {
        instantSearch: _.debounce(() => {
            VueEvent.$emit('searching')
        }, 1000),
        search() {
            VueEvent.$emit('searching')
        }
    }
});
