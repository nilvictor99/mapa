import heapq
import folium
import requests
from geopy.geocoders import Nominatim
from geopy.distance import geodesic
import time

class RouteGraph:
    def __init__(self):
        self.edges = {}
        self.weights = {}

    def add_edge(self, from_node, to_node, distance, traffic_factor=1.0, weather_factor=1.0):
        adjusted_weight = distance * traffic_factor * weather_factor
        if from_node not in self.edges:
            self.edges[from_node] = []
        if to_node not in self.edges:
            self.edges[to_node] = []
        
        self.edges[from_node].append(to_node)
        self.edges[to_node].append(from_node)
        
        self.weights[(from_node, to_node)] = adjusted_weight
        self.weights[(to_node, from_node)] = adjusted_weight

def dijkstra(graph, start):
    queue = []
    heapq.heappush(queue, (0, start))
    distances = {node: float('infinity') for node in graph.edges}
    distances[start] = 0
    shortest_path = {}

    while queue:
        current_distance, current_node = heapq.heappop(queue)
        if current_distance > distances[current_node]:
            continue

        for neighbor in graph.edges[current_node]:
            weight = graph.weights[(current_node, neighbor)]
            distance = current_distance + weight

            if distance < distances[neighbor]:
                distances[neighbor] = distance
                shortest_path[neighbor] = current_node
                heapq.heappush(queue, (distance, neighbor))

    return distances, shortest_path

def get_distance_between_locations(loc1, loc2):
    return geodesic(loc1, loc2).kilometers

def get_location_coordinates(location_name):
    geolocator = Nominatim(user_agent="route-optimizer")
    location = geolocator.geocode(location_name)
    if location:
        return (location.latitude, location.longitude)
    return None

def get_weather_factor(api_key, location):
    try:
        url = f"http://api.weatherapi.com/v1/current.json?key={api_key}&q={location}&aqi=no"
        response = requests.get(url)
        response.raise_for_status()
        weather_data = response.json()
        temperature = weather_data['current']['temp_c']
        if temperature < 15:
            return 1.2
        else:
            return 1.0
    except requests.exceptions.RequestException:
        return 1.0

def get_traffic_factor(ors_api_key, loc1, loc2):
    try:
        headers = {
            'Authorization': ors_api_key,
            'Content-Type': 'application/json'
        }
        body = {
            "coordinates": [
                [loc1[1], loc1[0]],
                [loc2[1], loc2[0]]
            ],
            "format": "json"
        }
        url = "https://api.openrouteservice.org/v2/directions/driving-car"
        response = requests.post(url, headers=headers, json=body)
        response.raise_for_status()
        
        route_data = response.json()
        distance_km = route_data['routes'][0]['summary']['distance'] / 1000
        duration_min = route_data['routes'][0]['summary']['duration'] / 60
        average_speed = distance_km / (duration_min / 60)

        if average_speed < 40:
            return 1.3
        elif average_speed < 60:
            return 1.1
        else:
            return 1.0
    except requests.exceptions.RequestException:
        return 1.0

def draw_route_on_map(route_graph, shortest_path, start_location, end_location, coordinates_map):
    map_center = coordinates_map[start_location]
    route_map = folium.Map(location=map_center, zoom_start=6)

    # Add only the start and end locations as markers
    for location in [start_location, end_location]:
        folium.Marker(coordinates_map[location], popup=location).add_to(route_map)

    route = [coordinates_map[start_location]]
    current_location = end_location
    while current_location != start_location:
        route.append(coordinates_map[current_location])
        current_location = shortest_path.get(current_location, start_location)

    route.reverse()
    folium.PolyLine(route, color='blue', weight=5, opacity=0.7).add_to(route_map)
    return route_map

# Initial configuration
route_graph = RouteGraph()
locations = {
    "Lima": get_location_coordinates("Lima, Peru"),
    "Cusco": get_location_coordinates("Cusco, Peru"),
    "Arequipa": get_location_coordinates("Arequipa, Peru"),
    "Trujillo": get_location_coordinates("Trujillo, Peru"),
    "Ica": get_location_coordinates("Ica, Peru")
}

weather_api_key = "dd2b55a4755a460fb17143354242610"
ors_api_key = "5b3ce3597851110001cf6248d16505ba0d5042b9a0ac331ff545dcff"

# Build the graph with traffic and weather factors
for from_location in locations:
    for to_location in locations:
        if from_location != to_location:
            distance = get_distance_between_locations(locations[from_location], locations[to_location])
            weather_factor = get_weather_factor(weather_api_key, from_location)
            traffic_factor = get_traffic_factor(ors_api_key, locations[from_location], locations[to_location])
            route_graph.add_edge(from_location, to_location, distance, traffic_factor=traffic_factor, weather_factor=weather_factor)
            time.sleep(1)

# Dynamic selection of start and end
start_location = input("Ingrese la ubicación de inicio (ej. 'Lima'): ")
end_location = input("Ingrese la ubicación de destino (ej. 'Cusco'): ")

if start_location in locations and end_location in locations:
    distances, shortest_path = dijkstra(route_graph, start_location)
    print(f"Distancia de {start_location} a {end_location}: {distances[end_location]:.2f} km")
    
    route_map = draw_route_on_map(route_graph, shortest_path, start_location, end_location, locations)
    route_map.save("ruta_mapa.html")
    print("Mapa guardado como ruta_mapa.html")
else:
    print("Ubicación no encontrada.")
