import routesMixin from './mixins/routes'

require('./bootstrap')

window.Vue = require('vue')

Vue.component('health-panel', require('./components/Panel.vue').default)

const app = new Vue({
    el: '#app',

    mixins: [routesMixin],

    data: {
        config: { loaded: false },
    },

    methods: {
        loadConfig() {
            let $this = this

            return axios.get($this.route('codicastudio.health.config')).then(function(response) {
                response.data.loaded = true

                $this.config = response.data

                $('.chart').css(
                    'height',
                    $this.config.database.graphs.height + 'px',
                )
            })
        },
    },

    mounted() {
        this.loadConfig()
    },
})
