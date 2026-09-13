import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  try {
    const response = await datatable.list(route('importers.index'), lazyParams);
    return response.data;
  } catch (error) {
    console.error(error);
    return false;
  }
};

const create = async (data) => await axios.post(route('importers.store'), data);

const update = async (id, data) => await axios.put(route('importers.update', { id }), data);

const del = async (id) => {
  await axios.delete(route('importers.destroy', { id }));
  return true;
};

export default {
  list,
  create,
  update,
  del,
};
