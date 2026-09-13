import { onUnmounted, reactive, ref } from 'vue';

export const useCollection = (fetchHandler, options = {}) => {
  const records = ref([]);
  const meta = ref({
    current_page: 1,
    from: 0,
    last_page: 1,
    per_page: options.perPage || 12,
    to: 0,
    total: 0,
  });
  const summary = ref({});
  const loading = ref(false);
  const error = ref('');
  const query = reactive({
    q: '',
    page: 1,
    per_page: options.perPage || 12,
    sort: options.sort || 'name',
    direction: options.direction || 'asc',
    ...(options.filters || {}),
  });

  let controller = null;
  let searchTimer = null;

  const load = async () => {
    controller?.abort();
    const requestController = new AbortController();
    controller = requestController;
    loading.value = true;
    error.value = '';

    try {
      const response = await fetchHandler({ ...query }, requestController.signal);
      records.value = response.items || [];
      meta.value = response.meta || meta.value;
      summary.value = response.summary || {};
    } catch (requestError) {
      if (requestError.code !== 'ERR_CANCELED') {
        error.value = 'No fue posible cargar los registros.';
        records.value = [];
      }
    } finally {
      if (!requestController.signal.aborted) {
        loading.value = false;
      }
    }
  };

  const resetPageAndLoad = () => {
    query.page = 1;
    return load();
  };

  const search = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(resetPageAndLoad, 250);
  };

  const setFilter = (key, value) => {
    clearTimeout(searchTimer);
    query[key] = value || '';
    return resetPageAndLoad();
  };

  const setPage = (page) => {
    if (page < 1 || page > meta.value.last_page || page === query.page) {
      return;
    }

    query.page = page;
    return load();
  };

  const setPerPage = (perPage) => {
    query.per_page = Number(perPage);
    return resetPageAndLoad();
  };

  const setSort = (sort) => {
    if (query.sort === sort) {
      query.direction = query.direction === 'asc' ? 'desc' : 'asc';
    } else {
      query.sort = sort;
      query.direction = 'asc';
    }

    return resetPageAndLoad();
  };

  onUnmounted(() => {
    controller?.abort();
    clearTimeout(searchTimer);
  });

  return {
    error,
    load,
    loading,
    meta,
    query,
    records,
    resetPageAndLoad,
    search,
    setFilter,
    setPage,
    setPerPage,
    setSort,
    summary,
  };
};
