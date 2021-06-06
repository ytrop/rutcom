<?php

namespace App\Http\Livewire;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Appointment;
use App\Http\Livewire\AdminComponent;

class Listappointments extends Component
{
    use WithPagination;
    protected $listeners = ['deleteConfirmed' => 'deleteAppointment'];

	public $appointmentIdBeingRemoved = null;

	public $status = null;

	protected $queryString = ['status'];

	public $selectedRows = [];

	public $selectPageRows = false;

	public function confirmAppointmentRemoval($appointmentId)
	{
		$this->appointmentIdBeingRemoved = $appointmentId;

		$this->dispatchBrowserEvent('show-delete-confirmation');
	}

	public function deleteAppointment()
	{
		$appointment = Appointment::findOrFail($this->appointmentIdBeingRemoved);

		$appointment->delete();

		$this->dispatchBrowserEvent('deleted', ['message' => 'Appointment deleted successfully!']);
	}

	public function filterAppointmentsByStatus($status = null)
	{
		$this->resetPage();

		$this->status = $status;
	}

	public function updatedSelectPageRows($value)
	{
		if ($value) {
			$this->selectedRows = $this->appointments->pluck('id')->map(function ($id) {
				return (string) $id;
			});
		} else {
			$this->reset(['selectedRows', 'selectPageRows']);
		}
	}

	public function getAppointmentsProperty()
	{
		return Appointment::with('client')
    		->when($this->status, function ($query, $status) {
    			return $query->where('status', $status);
    		})
    		->latest()
    		->paginate(3);
	}

	public function markAllAsScheduled()
	{
		Appointment::whereIn('id', $this->selectedRows)->update(['status' => 'SCHEDULED']);

		$this->dispatchBrowserEvent('updated', ['message' => 'Appointments marked as scheduled']);

		$this->reset(['selectPageRows', 'selectedRows']);
	}

	public function markAllAsClosed()
	{
		Appointment::whereIn('id', $this->selectedRows)->update(['status' => 'CLOSED']);

		$this->dispatchBrowserEvent('updated', ['message' => 'Appointments marked as closed.']);

		$this->reset(['selectPageRows', 'selectedRows']);
	}

	public function deleteSelectedRows()
	{
		Appointment::whereIn('id', $this->selectedRows)->delete();

		$this->dispatchBrowserEvent('deleted', ['message' => 'All selected appointment got deleted.']);

		$this->reset(['selectPageRows', 'selectedRows']);
	}

    public function render()

    {   $appointments = $this->appointments;

    	$appointmentsCount = Appointment::count();
    	$scheduledAppointmentsCount = Appointment::where('status', 'scheduled')->count();
    	$closedAppointmentsCount = Appointment::where('status', 'closed')->count();

        return view('livewire.listappointments', [
        	'appointments' => $appointments,
        	'appointmentsCount' => $appointmentsCount,
        	'scheduledAppointmentsCount' => $scheduledAppointmentsCount,
        	'closedAppointmentsCount' => $closedAppointmentsCount,
        ]);

    }
}
