(function () {
    const apiBase = '/api/v1/locations';

    async function fetchData(path) {
        const response = await fetch(path);
        const payload = await response.json();
        return payload.data || {};
    }

    function flagEmoji(iso) {
        if (!iso || iso.length !== 2) {
            return '';
        }
        const upper = iso.toUpperCase();
        return String.fromCodePoint(
            upper.charCodeAt(0) + 127397,
            upper.charCodeAt(1) + 127397
        );
    }

    function normalizeCountryItem(item) {
        if (typeof item === 'string') {
            return { name: item, iso: '', flag: '' };
        }

        return {
            name: item.name || '',
            iso: item.iso || '',
            flag: item.flag || flagEmoji(item.iso || ''),
        };
    }

    function fillSelect(select, items, placeholder, selected, withFlags) {
        select.innerHTML = '';
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = placeholder;
        select.appendChild(empty);

        items.forEach((rawItem) => {
            const item = typeof rawItem === 'string'
                ? { name: rawItem, iso: '', flag: '' }
                : rawItem;
            const option = document.createElement('option');
            option.value = item.name;
            option.dataset.iso = item.iso || '';
            option.textContent = withFlags ? item.name : item.name;
            if (item.name === selected) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    }

    async function initPicker(picker) {
        const countrySelect = picker.querySelector('.loc-country');
        const citySelect = picker.querySelector('.loc-city');
        const districtSelect = picker.querySelector('.loc-district');
        const districtWrap = picker.querySelector('.loc-district-wrap');
        const showDistrict = picker.dataset.showDistrict !== '0';

        const initialCountry = picker.dataset.country || '';
        const initialCity = picker.dataset.city || '';
        const initialDistrict = picker.dataset.district || '';

        const { countries = [], countries_meta = [] } = await fetchData(`${apiBase}/countries`);
        const source = countries_meta.length ? countries_meta : countries;
        const countryItems = source.map(normalizeCountryItem);
        fillSelect(countrySelect, countryItems, 'Ülke', initialCountry, true);
        if (window.gkSyncFlaggedSelect) {
            window.gkSyncFlaggedSelect(countrySelect);
        }

        async function loadCities(country, selectedCity) {
            if (!country) {
                fillSelect(citySelect, [], 'Şehir', '');
                citySelect.disabled = true;
                return;
            }

            const { cities = [] } = await fetchData(
                `${apiBase}/cities?country=${encodeURIComponent(country)}`
            );
            fillSelect(citySelect, cities, 'Şehir', selectedCity);
            citySelect.disabled = false;
        }

        async function loadDistricts(country, city, selectedDistrict) {
            if (!showDistrict || !districtSelect || !districtWrap) {
                return;
            }

            if (!country || !city) {
                districtWrap.hidden = true;
                districtSelect.required = false;
                districtSelect.value = '';
                return;
            }

            const { districts = [] } = await fetchData(
                `${apiBase}/districts?country=${encodeURIComponent(country)}&city=${encodeURIComponent(city)}`
            );

            if (!districts.length) {
                districtWrap.hidden = true;
                districtSelect.required = false;
                districtSelect.value = '';
                return;
            }

            districtWrap.hidden = false;
            districtSelect.required = true;
            fillSelect(districtSelect, districts, 'İlçe', selectedDistrict);
        }

        countrySelect.addEventListener('change', async () => {
            if (window.gkSyncFlaggedSelect) {
                window.gkSyncFlaggedSelect(countrySelect);
            }
            await loadCities(countrySelect.value, '');
            await loadDistricts(countrySelect.value, '', '');
        });

        citySelect.addEventListener('change', async () => {
            await loadDistricts(countrySelect.value, citySelect.value, '');
        });

        if (initialCountry) {
            await loadCities(initialCountry, initialCity);
            if (showDistrict) {
                await loadDistricts(initialCountry, initialCity, initialDistrict);
            }
        } else {
            citySelect.disabled = true;
            if (districtWrap) {
                districtWrap.hidden = true;
            }
        }
    }

    document.querySelectorAll('[data-location-picker]').forEach(initPicker);
})();
