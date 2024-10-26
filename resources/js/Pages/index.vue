<template>
    <app-layout>



        <div class="py-12">

            <div id="map" class="w-full max-w-[600px] h-[400px] mx-auto my-4"></div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <form @submit.prevent="searchLocation" class="space-y-4 mt-6">
                        <h3 class="text-lg font-medium text-gray-900">Search Location</h3>
                        <input v-model="searchQuery" @input="autocomplete" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Search for a location" required>

                        <ul v-if="autocompleteResults.length"
                            class="border border-gray-300 rounded-md mt-2 max-h-60 overflow-y-auto">
                            <li v-for="result in autocompleteResults" :key="result.place_id"
                                @click="selectLocation(result)" class="p-2 hover:bg-gray-200 cursor-pointer">
                                {{ result.display_name }}
                            </li>
                        </ul>
                    </form>
                    <form @submit.prevent="addMarkers" class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Agregar Marcadores para Inicio y Fin</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Latitud de Inicio</label>
                                <input v-model="startLat" type="number" step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Longitud de Inicio</label>
                                <input v-model="startLng" type="number" step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Latitud de Fin</label>
                                <input v-model="endLat" type="number" step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Longitud de Fin</label>
                                <input v-model="endLng" type="number" step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                        </div>
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Agregar Marcadores
                        </button>

                        <button @click.prevent="optimizeRoute" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
            Calculate Optimal Route
        </button>
                    </form>

                    <form @submit.prevent="addCircle" class="space-y-4 mt-6">
                        <h3 class="text-lg font-medium text-gray-900">Add Circle</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Latitude</label>
                                <input v-model="circleLat" type="number" step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Longitude</label>
                                <input v-model="circleLng" type="number" step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Radius (meters)</label>
                                <input v-model="circleRadius" type="number"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                        </div>
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Add Circle
                        </button>
                    </form>

                    <!-- Results Section -->
                    <div v-if="route" class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900">Detalles de la Ruta Óptima</h3>
                        <div class="mt-4 bg-gray-50 p-4 rounded-md">
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tiempo Estimado</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ route.estimatedTime }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Emisiones de CO₂</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ route.co2Emissions }} kg</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Consumo de Combustible</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ route.fuelConsumption }} L</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Distancia</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ route.distance }} km</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

export default defineComponent({
    components: {
        AppLayout,
    },

    data() {
        return {
      searchQuery: '',
      autocompleteResults: [],
      route: null,
      startLat: null,
      startLng: null,
      endLat: null,
      endLng: null,
      circleLat: null,
      circleLng: null,
      circleRadius: null,
      map: null,
        }
    },

    deg2rad(deg) {
        return deg * (Math.PI / 180);
    },

    mounted() {
        // Load Leaflet CSS
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        // Load Leaflet JS
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => {
            this.initializeMap();
        };
        document.head.appendChild(script);
    },

    methods: {
    async optimizeRoute() {
      try {
        const response = await this.$inertia.post('python/route_obtimizer.py', {
          start: {
            lat: this.startLat,
            lng: this.startLng,
          },
          end: {
            lat: this.endLat,
            lng: this.endLng,
          },
         
        });
        this.route = response.data;
      } catch (error) {
        console.error('Error optimizing route:', error);
      }
    },

        initializeMap() {
            this.map = L.map('map').setView([51.505, -0.09], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(this.map);

            const popup = L.popup();
            this.map.on('click', (e) => {
                popup
                    .setLatLng(e.latlng)
                    .setContent("You clicked the map at " + e.latlng.toString())
                    .openOn(this.map);
            });
        },

        async autocomplete() {
            if (this.searchQuery.length < 3) {
                this.autocompleteResults = [];
                return;
            }

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${this.searchQuery}`);
                const results = await response.json();
                this.autocompleteResults = results;
            } catch (error) {
                console.error('Error fetching autocomplete results:', error);
            }
        },

        selectLocation(result) {
      if (!this.startLat) {
        this.startLat = result.lat;
        this.startLng = result.lon;
        this.addMarker('Inicio', this.startLat, this.startLng);
      } else {
        this.endLat = result.lat;
        this.endLng = result.lon;
        this.addMarker('Fin', this.endLat, this.endLng);
        this.calculateRoute();
      }
    },

    addMarkers() {
      this.addMarker('Inicio', this.startLat, this.startLng);
      this.addMarker('Fin', this.endLat, this.endLng);
      this.calculateRoute();
    },

    addMarker(type, lat, lng) {
      const marker = L.marker([lat, lng]).addTo(this.map);
      marker.bindPopup(`<b>${type}</b><br>Latitud: ${lat}<br>Longitud: ${lng}`).openPopup();
    },



       async calculateRoute() {
      const data = {
        start: { lat: this.startLat, lng: this.startLng },
        end: { lat: this.endLat, lng: this.endLng },
      };

      try {
        const response = await fetch('routes.optimize', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        });
        this.route = await response.json();
      } catch (error) {
        console.error('Error calculating route:', error);
      }
        },
        addCircle() {
            const circle = L.circle([this.circleLat, this.circleLng], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: this.circleRadius
            }).addTo(this.map);
            circle.bindPopup("Circle with radius " + this.circleRadius + " meters.").openPopup();

            this.circleLat = null;
            this.circleLng = null;
            this.circleRadius = null;
        },
    }
})
</script>
