/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import BootstrapVue from 'bootstrap-vue';

window.Vue = require('vue');
Vue.use(BootstrapVue);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',

    data: {
        checked: [],
        filters: window.filtersActive
    },

    methods: {
        confirmation(event) {
            event.preventDefault();

            if (this.checked.length == 0) {
                return;
            }

            this.$bvModal.msgBoxConfirm('Please confirm that you want to delete checked scans.', {
                title: 'Please Confirm',
                //size: 'lg',
                buttonSize: 'sm',
                okVariant: 'danger',
                okTitle: 'CONFIRM',
                cancelTitle: 'CANCEL',
                footerClass: 'p-2',
                hideHeaderClose: true,
                centered: true
            }).then(value => {
                if (value) {
                    event.srcElement.submit();
                }
            });

        },

        form_link_confirmation(event, formId) {
            event.preventDefault();
            this.$bvModal.msgBoxConfirm('Please confirm that you want to delete this scan', {
                title: 'Please Confirm',
                //size: 'lg',
                buttonSize: 'sm',
                okVariant: 'danger',
                okTitle: 'CONFIRM',
                cancelTitle: 'CANCEL',
                footerClass: 'p-2',
                hideHeaderClose: true,
                centered: true
            }).then(value => {
                if (value) {
                    document.getElementById(formId).submit();
                }
            });
        },

        form_submit(event, formId) {
            event.preventDefault();
            document.getElementById(formId).submit();
        },

        form_job_flush_confirmation(event, formId) {
            event.preventDefault();
            this.$bvModal.msgBoxConfirm('Please confirm that you want to delete all jobs', {
                title: 'Please Confirm',
                //size: 'lg',
                buttonSize: 'sm',
                okVariant: 'danger',
                okTitle: 'CONFIRM',
                cancelTitle: 'CANCEL',
                footerClass: 'p-2',
                hideHeaderClose: true,
                centered: true
            }).then(value => {
                if (value) {
                    document.getElementById(formId).submit();
                }
            });
        },

    }
});
