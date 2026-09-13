import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  const response = await datatable.list(route('dogs.index'), lazyParams);

  return response.data;
};

const del = async (id) => {
  await axios.delete(route('dogs.destroy', { id }));
  return true;
};

export default {
  list,
  del,
};
