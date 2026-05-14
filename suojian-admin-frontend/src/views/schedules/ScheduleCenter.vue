<template>
  <div>
    <div class="page-header">
      <div><h2>排课中心</h2><p>管理小班/班课/1v1/托管排课</p></div>
      <div class="header-actions">
        <el-select v-model="typeFilter" placeholder="课程类型" @change="loadData" style="width:140px;margin-right:8px">
          <el-option label="全部" :value="0" />
          <el-option label="小班/班课" :value="1" />
          <el-option label="1v1" :value="2" />
          <el-option label="托管" :value="3" />
        </el-select>
        <el-button type="primary" @click="showAdd=true">+ 添加排课</el-button>
      </div>
    </div>
    <el-card shadow="never">
      <el-table :data="list" stripe v-loading="loading" border>
        <el-table-column prop="type" label="类型" width="80" align="center">
          <template #default="{row}"><el-tag :type="typeTag(row.type)" size="small">{{typeLabel(row.type)}}</el-tag></template>
        </el-table-column>
        <el-table-column prop="course_id" label="课程" width="100"><template #default="{row}">{{row.course_name||'-'}}</template></el-table-column>
        <el-table-column prop="teacher_id" label="老师" width="100"><template #default="{row}">{{row.teacher_name||'-'}}</template></el-table-column>
        <el-table-column prop="student_id" label="学员" width="100"><template #default="{row}">{{row.student_name||'-'}}</template></el-table-column>
        <el-table-column label="时间" width="200">
          <template #default="{row}">周{{weekName(row.week_day)}} {{row.start_time}}-{{row.end_time}}</template>
        </el-table-column>
        <el-table-column prop="room" label="教室" width="100" />
        <el-table-column prop="capacity" label="容量" width="70" align="center"><template #default="{row}">{{row.enrolled||0}}/{{row.capacity||'-'}}</template></el-table-column>
        <el-table-column prop="status" label="状态" width="70" align="center">
          <template #default="{row}"><el-tag :type="row.status==1?'success':'info'" size="small">{{row.status==1?'启用':'停用'}}</el-tag></template>
        </el-table-column>
        <el-table-column label="操作" width="120" fixed="right">
          <template #default="{row}">
            <el-button text type="primary" size="small" @click="editRow(row)">编辑</el-button>
            <el-button text type="danger" size="small" @click="deleteRow(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="table-footer"><el-pagination v-model:current-page="page" :page-size="pageSize" :total="total" layout="total,prev,pager,next" background small /></div>
    </el-card>
    <el-dialog v-model="showAdd" :title="editId?'编辑排课':'添加排课'" width="600px">
      <el-form :model="form" label-width="100px" class="dialog-form">
        <el-form-item label="课程类型" required>
          <el-radio-group v-model="form.type"><el-radio :value="1">小班/班课</el-radio><el-radio :value="2">1v1</el-radio><el-radio :value="3">托管</el-radio></el-radio-group>
        </el-form-item>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="老师" required><el-select v-model="form.teacher_id" filterable style="width:100%"><el-option v-for="t in teachers" :key="t.id" :label="t.username" :value="t.id" /></el-select></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="教室"><el-select v-model="form.room_id" clearable style="width:100%"><el-option v-for="r in rooms" :key="r.id" :label="r.name" :value="r.id" /></el-select></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="8"><el-form-item label="星期" required><el-select v-model="form.week_day"><el-option v-for="(n,i) in ['一','二','三','四','五','六','日']" :key="i+1" :label="'周'+n" :value="i+1" /></el-select></el-form-item></el-col>
          <el-col :span="8"><el-form-item label="开始" required><el-time-picker v-model="form.start_time" format="HH:mm" value-format="HH:mm:ss" style="width:100%" /></el-form-item></el-col>
          <el-col :span="8"><el-form-item label="结束" required><el-time-picker v-model="form.end_time" format="HH:mm" value-format="HH:mm:ss" style="width:100%" /></el-form-item></el-col>
        </el-row>
        <el-row :gutter="16">
          <el-col :span="12"><el-form-item label="开始日期"><el-date-picker v-model="form.start_date" type="date" value-format="YYYY-MM-DD" style="width:100%" /></el-form-item></el-col>
          <el-col :span="12"><el-form-item label="结束日期"><el-date-picker v-model="form.end_date" type="date" value-format="YYYY-MM-DD" style="width:100%" /></el-form-item></el-col>
        </el-row>
        <el-form-item label="班级容量"><el-input-number v-model="form.capacity" :min="0" :max="50" style="width:120px" /></el-form-item>
        <el-form-item label="教室名称"><el-input v-model="form.room" placeholder="如: 301室" /></el-form-item>
      </el-form>
      <template #footer><el-button @click="showAdd=false">取消</el-button><el-button type="primary" @click="handleSave" :loading="saving">保存</el-button></template>
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import http from '../../utils/http'
const BASE='http://47.114.125.123'
const list=ref([]),page=ref(1),pageSize=15,total=ref(0),loading=ref(false)
const showAdd=ref(false),saving=ref(false),editId=ref(0)
const typeFilter=ref(0)
const teachers=ref([]),rooms=ref([])
const form=reactive({type:1,teacher_id:0,room_id:0,week_day:1,start_time:'',end_time:'',start_date:'',end_date:'',capacity:0,room:'',course_id:0,student_id:0,grade_id:0})
async function loadData(){
  loading.value=true
  try{
    const params={page:page.value,pageSize,type:typeFilter.value}
    const res=await http.get(BASE+'/m/Admin/c/Api/a/scheduleList',{params})
    list.value=res.data.list||[];total.value=res.data.total||0
  }catch{e=>console.error(e)}
  finally{loading.value=false}
}
async function loadTeachers(){
  const res=await http.get(BASE+'/m/Admin/c/Api/a/teachers')
  teachers.value=res.data.list||[]
}
async function loadRooms(){
  const res=await http.get(BASE+'/m/Admin/c/Api/a/roomList')
  rooms.value=res.data.list||[]
}
function typeLabel(t){return['','小班/班课','1v1','托管'][t]||'未知'}
function typeTag(t){return['','success','primary','warning'][t]||'info'}
function weekName(d){return['','一','二','三','四','五','六','日'][d]||''}
function editRow(row){Object.assign(form,row);editId.value=row.id;showAdd.value=true}
async function deleteRow(row){
  await ElMessageBox.confirm('确定删除该排课？','确认')
  await http.get(BASE+'/m/Admin/c/Api/a/scheduleDelete',{params:{id:row.id}})
  ElMessage.success('已删除');loadData()
}
async function handleSave(){
  if(!form.teacher_id||!form.start_time||!form.end_time){ElMessage.warning('请填写完整');return}
  saving.value=true
  try{
    const params=new URLSearchParams()
    Object.entries(form).forEach(([k,v])=>{if(v!==''&&v!==null&&v!==undefined)params.append(k,v)})
    if(editId.value)params.append('id',editId.value)
    const url=BASE+'/m/Admin/c/Api/a/'+(editId.value?'scheduleUpdate':'scheduleCreate')
    await http.post(url,params.toString(),{headers:{'Content-Type':'application/x-www-form-urlencoded'}})
    ElMessage.success(editId.value?'更新成功':'添加成功')
    showAdd.value=false;editId.value=0;loadData()
  }catch(e){console.error(e);ElMessage.error('操作失败')}
  finally{saving.value=false}
}
onMounted(()=>{loadData();loadTeachers();loadRooms()})
</script>
<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.header-actions{display:flex;align-items:center}
.page-header h2{font-size:22px;font-weight:600;color:#1a1a2e;margin-bottom:4px}
.page-header p{font-size:13px;color:#909399}
.table-footer{display:flex;justify-content:flex-end;padding:16px 0 0}
.dialog-form{padding:10px 0}
</style>
