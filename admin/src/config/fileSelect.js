import systemApi from "@/api/admin/system.js";

//文件选择器配置
export default {
  apiObj: systemApi.file.upload,
  menuApiObj: systemApi.file.menu,
  addMenuApiObj: systemApi.file.addMenu,
  deleteMenuApiObj: systemApi.file.deleteMenu,
  listApiObj: systemApi.file.list,
  deleteApiObj: systemApi.file.delete,
  successCode: 0,
  maxSize: 10,
  max: 99,
  uploadParseData: function (res) {
    return {
      id: res.data.id,
      fileName: res.data.fileName,
      url: res.data.src
    }
  },
  listParseData: function (res) {
    return {
      rows: res.data.rows,
      total: res.data.total,
      msg: res.message,
      code: res.code
    }
  },
  request: {
    page: 'page',
    pageSize: 'page_size',
    keyword: 'keyword',
    menuKey: 'menu_id'
  },
  menuProps: {
    key: 'id',
    label: 'name',
    children: 'children'
  },
  fileProps: {
    key: 'id',
    fileName: 'file_name',
    url: 'url'
  },
  files: {
    doc: {
      icon: 'sc-icon-file-word-2-fill',
      color: '#409eff'
    },
    docx: {
      icon: 'sc-icon-file-word-2-fill',
      color: '#409eff'
    },
    xls: {
      icon: 'sc-icon-file-excel-2-fill',
      color: '#67C23A'
    },
    xlsx: {
      icon: 'sc-icon-file-excel-2-fill',
      color: '#67C23A'
    },
    ppt: {
      icon: 'sc-icon-file-ppt-2-fill',
      color: '#F56C6C'
    },
    pptx: {
      icon: 'sc-icon-file-ppt-2-fill',
      color: '#F56C6C'
    }
  }
}
