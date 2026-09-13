import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  const response = await datatable.list(route('security_equipments.index'), lazyParams);

  return response.data;
};

const del = async (id) => {
  await axios.delete(route('security_equipments.destroy', { id }));
  return true;
};

export default {
  list,
  del,
};
