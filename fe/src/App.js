import React, { Component } from 'react';
import { BrowserRouter, Route, Link } from 'react-router-dom'
import './App.css';

class App extends Component {
  render() {
    return (
      <BrowserRouter>
        <div className="App">
          <div className="Navigation">
            <Link to="/">Home</Link>
            <Link to="/post">Post</Link>
          </div>
          <Route exact path="/" component={Homepage} />
          <Route path="/post" component={Post} />
          <Route path="/job/:job" component={JobPage} />
        </div>
      </BrowserRouter>
    );
  }
}

class Homepage extends Component {
  constructor(props) {
    super(props);

    this.state = {
      jobs: [],
      loading: false,
    };
  };

  componentDidMount() {
    this.setState({ loading: true });
    fetch("https://jobsapi.gmem.ca/")
      .then(response => response.json())
      .then(data => this.setState({ jobs: data, loading: false }));
  }
  render() {
    const { loading } = this.state;

    if (loading) {
      return <div className="LoadingSpinner"><div></div><div></div><div></div><div></div></div>;
    }
    return (
      <List jobs={this.state.jobs} />
    )
  }
}

class List extends Component {
  render() {
    let listings = this.props.jobs.map((job, index) => {
      return <JobListItem job={job} key={index} />
    });
    return (
      <div className="JobList">
        {listings}
      </div>
    );
  }
}

class JobListItem extends Component {
  render() {
    let job = this.props.job;
    return (
      <div className="Job">
        <h3><Link to={`job/${job.id}`}>{job.position}</Link> <small>{job.company_name}</small></h3>
        <p>Location: {job.location}</p>
        <p>Remote? {job.remote === true ? '✔️' : '✘'}</p>
      </div>
    )
  }
}

class JobPage extends Component {
  constructor(props) {
    super(props);

    this.state = {
      job: {},
      loading: false,
    };
  };

  componentDidMount() {
    const jobId = this.props.match.params.job;
    this.setState({ loading: true });
    fetch("https://jobsapi.gmem.ca/" + jobId)
      .then(response => response.json())
      .then(data => this.setState({ loading: false, job: data }));
  }

  render() {
    let job = this.state.job;

    return (
      <div className="SingleJob">
        <h3>{job.position} <small>{job.company_name}</small></h3>
        <p>Location: {job.location}</p>
        <p>Remote? {job.remote === true ? '✔️' : '✘'}</p>
        <p>{job.additional}</p>
      </div>
    )
  }
}

class Post extends Component {
  render() {
    return (
      <div className="App">
        <h2>Post new job</h2>
        <form action="https://jobsapi.gmem.ca" method="POST">
          <label>Position
          <input name="position" id="position" type="text" />
          </label>
          <label>Company Name
          <input name="company_name" id="company_name" type="text" />
          </label>
          <label>Location
          <input name="location" id="location" type="text" />
          </label>
          <label>Apply Link
          <input name="apply" id="apply" type="text" />
          </label>
          <label>Remote?
          <input type="checkbox" name="remote" id="remote" />
          </label>
          <label>Additional Details
          <textarea cols="33" rows="10" resizeable="false" name="additional" id="additional"></textarea>
          </label>
          <input type="submit" value="Submit" />
        </form>
      </div>
    );
  }
}

export default App;
