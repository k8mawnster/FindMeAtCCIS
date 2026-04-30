@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <a href="{{ route('login') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <div class="form-title">Student Register</div>

    @if(session('error'))
        <p style="color: var(--color-red); text-align: center; margin-bottom: 15px; font-weight: bold;">
            {{ session('error') }}
        </p>
    @endif

    <form action="{{ route('register.post') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="student_id">Student ID</label>
            <input type="text" id="student_id" name="student_id"
                placeholder="XX-XXXXXX" maxlength="9" required value="{{ old('student_id') }}">
        </div>

        <div class="form-group">
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname"
                required value="{{ old('fullname') }}">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                placeholder="student@example.com" required value="{{ old('email') }}">
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number"
                placeholder="+639XX XXX XXXX" required value="{{ old('phone_number') }}">
        </div>

        <div class="form-group">
            <label for="course">Course</label>
            <select id="course" name="course" required onchange="loadYearLevels(this.value)">
                <option value="" disabled selected>Choose Course</option>
                @foreach($courses as $course)
                    <option value="{{ $course->course_id }}"
                        {{ old('course') == $course->course_id ? 'selected' : '' }}>
                        {{ $course->course_code }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="year-group" style="display: none;">
            <label for="year_level">Year Level</label>
            <select id="year_level" name="year_level" onchange="loadSections()">
                <option value="" disabled selected>Select Year</option>
            </select>
        </div>

        <div class="form-group" id="section-group" style="display: none;">
            <label for="section">Section</label>
            <select id="section" name="section">
                <option value="" disabled selected>Select Section</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-register" style="color: var(--color-white);">REGISTER</button>
    </form>
@endsection

@section('scripts')
<script>
    const courseSectionData = {
        "1": {
            "1": ["A", "B", "C"],
            "2": ["A", "B", "C"],
            "3": ["A", "B", "C"],
            "4": ["A", "B"]
        },
        "2": {
            "1": ["A", "B", "C"],
            "2": ["A", "B"],
            "3": ["A"],
            "4": ["A"]
        }
    };

    const yearLevelMap = {
        "1": "1st Year", "2": "2nd Year", "3": "3rd Year", "4": "4th Year"
    };

    function loadYearLevels(courseId) {
        const yearSelect = document.getElementById('year_level');
        const yearGroup  = document.getElementById('year-group');

        yearSelect.innerHTML = '<option value="" disabled selected>Select Year</option>';
        document.getElementById('section-group').style.display = 'none';

        if (courseId && courseSectionData[courseId]) {
            yearGroup.style.display = 'block';
            for (const year in courseSectionData[courseId]) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = yearLevelMap[year];
                yearSelect.appendChild(option);
            }
        } else {
            yearGroup.style.display = 'none';
        }
    }

    function loadSections() {
        const courseId = document.getElementById('course').value;
        const year = document.getElementById('year_level').value;
        const sectionSelect = document.getElementById('section');
        const sectionGroup  = document.getElementById('section-group');

        sectionSelect.innerHTML = '<option value="" disabled selected>Select Section</option>';

        if (courseId && year && courseSectionData[courseId]?.[year]) {
            sectionGroup.style.display = 'block';
            courseSectionData[courseId][year].forEach(sec => {
                const option = document.createElement('option');
                option.value = sec;
                option.textContent = sec;
                sectionSelect.appendChild(option);
            });
        } else {
            sectionGroup.style.display = 'none';
        }
    }

    document.getElementById('student_id').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length > 2) {
            value = value.substring(0, 2) + '-' + value.substring(2, 8);
        }
        e.target.value = value;
    });
</script>
@endsection