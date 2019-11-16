<template>
<v-layout row justify-center>

    <v-dialog v-model="dialog" persistent width="1200px">
        <v-card v-if="dialog">
            <!-- <v-card-title>
                Update Shipment
                <v-spacer></v-spacer>
                <v-btn icon dark @click="close">
                    <v-icon color="black">close</v-icon>
                </v-btn>
            </v-card-title> -->
            <v-container grid-list-md>
                <v-card style="width: 100%;">
                    <GmapMap style="width: 1100px; height: 500px; margin: auto" :zoom="12" :center="{lat: -1.28333, lng: 36.81667}">
                        <GmapMarker v-for="(marker, index) in markers" :key="index" :label="marker.lable" :position="marker.position" :options="{
                                zoomControl: true,
                                mapTypeControl: true,
                                scaleControl: true,
                                streetViewControl: true,
                                rotateControl: true,
                                fullscreenControl: true,
                                disableDefaultUi: false
                                }" />
                        <!-- <GmapMarker v-if="this.place" label="test" :position="{
                                lat: this.place.geometry.location.lat(),
                                lng: this.place.geometry.location.lng(),
                                }" :options="{
                                zoomControl: true,
                                mapTypeControl: false,
                                scaleControl: false,
                                streetViewControl: false,
                                rotateControl: false,
                                fullscreenControl: true,
                                disableDefaultUi: false
                                }" /> -->
                    </GmapMap>
                    <v-divider></v-divider>
                </v-card>
                <v-card-actions>
                    <v-btn flat @click="close">Close</v-btn>
                    <v-flex xs4 sm3>
                        <v-text-field v-model="dist" color="blue darken-2" label="Distance" ref="distanceGet" required></v-text-field>
                    </v-flex>
                    <v-btn flat @click="calcCrow">Dist</v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-container>
        </v-card>
    </v-dialog>
</v-layout>
</template>

<script>
import VueGoogleAutocomplete from "vue-google-autocomplete";
export default {
    // props: ['markers'],
    components: {
        VueGoogleAutocomplete,
    },
    data() {
        return {
            // labele_: 'labele_',
            loading: false,
            dialog: false,
            loading_loc: false,
            markers: [],
            statuses: [],
            showMap: false,
            place: null,
            dist: ""
        };
    },
    description: "Maps",

    methods: {
        locationUpdate() {
            this.loading_loc = true;
            this.updateitedItem.location = this.updateitedItem.location;
            axios
                .post(`/locationUpdate/${this.updateitedItem.id}`, {
                    markers: this.markers,
                    dist: this.dist,
                })
                .then(response => {
                    this.loading_loc = false;
                    this.alert();
                    // Object.assign(this.$parent.AllShipments[this.$parent.editedIndex], this.updateitedItem)
                })
                .catch(error => {
                    this.loading_loc = false;
                    if (error.response.status === 500) {
                        eventBus.$emit('errorEvent', error.response.statusText)
                        return
                    } else if (error.response.status === 401 || error.response.status === 409) {
                        eventBus.$emit('reloadRequest', error.response.statusText)
                    }
                    this.errors = error.response.data.errors;
                });
        },
        // getAddressData: function(addressData, placeResultData, id) {
        //   this.address = addressData;
        // },

        close() {
            this.dialog = false;
        },
        setDescription(description) {
            this.description = description;
        },
        setPlace(place) {
            this.place = place;
        },
        usePlace(place) {
            if (this.place) {
                this.markers.push({
                    position: {
                        lat: this.place.geometry.location.lat(),
                        lng: this.place.geometry.location.lng()
                    }
                });
                this.place = null;
            }
        },
        mapUpsd() {
            this.markers = [];
            this.showMap = true;
        },
        getDistance() {
            this.dist = computeDistanceBetween((-1.2920659, 36.82194619999996), (-4.0434771, 39.6682065));
            return

            var dist = []
            for (let i = 0; i < this.markers.length; i++) {
                const element = this.markers[i];
                // console.log(element)
                // alert(element['position']['lat'])
                var p1 = new google.maps.LatLng(element['position']['lat'], element['position']['lng']);
                // var p2 = new google.maps.LatLng(element['position']['lat'], element['position']['lng']);
                dist.push(p1)
                // alert(p1)
                // var p2 = new google.maps.LatLng(-4.05052, 39.667169);
            }

            alert(dist)
            console.log(computeDistanceBetween(dist))

            function calcDistance(dist) {
                return this.dist = (google.maps.geometry.spherical.computeDistanceBetween(dist) / 1000).toFixed(2);
            }
        },

        calcCrow(lat1, lon1, lat2, lon2) {

            var dist = []
            for (let i = 0; i < this.markers.length; i++) {
                const element = this.markers[i];
                var p1 = element['position']['lat'];
                var l1 = element['position']['lng'];
                dist.push(p1, l1)
            }
            var lat1 = dist[0]
            var lon1 = dist[1]
            var lat2 = dist[2]
            var lon2 = dist[3]
            var R = 6371; // km
            var dLat = this.toRad(lat2 - lat1);
            var dLon = this.toRad(lon2 - lon1);
            var lat1 = this.toRad(lat1);
            var lat2 = this.toRad(lat2);

            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(lat1) * Math.cos(lat2);
            console.log(a);

            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            console.log(c);

            var d = R * c;
            this.dist = d
            return d;
        },

        // Converts numeric degrees to radians
        toRad(Value) {
            return Value * Math.PI / 180;
        },
        getMarkers(data) {
            axios
                .post(`/getcoordinatesArray/${data.id}`)
                .then(response => (this.markers = response.data))
                .catch(error => (this.errors = error.response.data.errors));
            console.log(this.coordinatesArr);
        }
    },
    created() {
        eventBus.$on('mapEvent', data => {
            this.dialog = true
            this.getMarkers(data)
        })
    },
};
</script>
