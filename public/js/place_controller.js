export default class extends Controller {
    static targets = [
        'campus', 'place', 'city',
        'street', 'postalCode', 'latitude', 'longitude'
    ];

    places = [];

    connect() {
        this.updatePlaces();
    }

    async updatePlaces()
    {
        const campusId = this.campusTarget.value;

        if (!campusId)
        {
            this.resetSelects();
            this.clearAddress();
            return;
        }

        const response = await fetch(`/places/by-campus/${campusId}`);
        this.places = await response.json();

        this.populateCities();
        this.populatePlaces(this.places);
        this.selectFirstPlace(this.places);
    }

    populateCities()
    {
        const cities = [...new Set(this.places.map(p => p.city))];

        this.cityTarget.innerHTML = '<option value="">Toutes les villes</option>';

        cities.forEach(city =>
        {
            this.cityTarget.insertAdjacentHTML(
                'beforeend',
                `<option value="${city}">${city}</option>`
            );
        });
    }

    populatePlaces(list)
    {
        this.placeTarget.innerHTML = '';

        list.forEach(place =>
        {
            this.placeTarget.insertAdjacentHTML(
                'beforeend',
                `<option value="${place.id}">${place.name}</option>`
            );
        });
    }

    filterPlaces()
    {
        const selectedCity = this.cityTarget.value;

        const filtered = selectedCity
            ? this.places.filter(p => p.city === selectedCity)
            : this.places;

        this.populatePlaces(filtered);
        this.selectFirstPlace(filtered);
    }


    selectFirstPlace(list)
    {
        if (list.length === 0)
        {
            this.clearAddress();
            return;
        }

        this.placeTarget.value = list[0].id;
        this.fillAddress();
    }

    fillAddress()
    {
        const placeId = parseInt(this.placeTarget.value, 10);
        const place = this.places.find(p => p.id === placeId);

        if (!place)
        {
            this.clearAddress();
            return;
        }

        this.streetTarget.value = place.street ?? '';
        this.postalCodeTarget.value = place.postalCode ?? '';
        this.latitudeTarget.value = place.latitude ?? '';
        this.longitudeTarget.value = place.longitude ?? '';
    }

    clearAddress()
    {
        this.streetTarget.value = '';
        this.postalCodeTarget.value = '';
        this.latitudeTarget.value = '';
        this.longitudeTarget.value = '';
    }

    resetSelects()
    {
        this.placeTarget.innerHTML = '<option value="">Sélectionnez un campus</option>';
        this.cityTarget.innerHTML = '<option value="">Toutes les villes</option>';
    }
}
