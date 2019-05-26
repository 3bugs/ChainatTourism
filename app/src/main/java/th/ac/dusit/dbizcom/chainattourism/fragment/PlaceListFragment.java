package th.ac.dusit.dbizcom.chainattourism.fragment;

import android.content.Context;
import android.graphics.Rect;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;

import java.util.List;

import retrofit2.Call;
import retrofit2.Retrofit;
import th.ac.dusit.dbizcom.chainattourism.R;
import th.ac.dusit.dbizcom.chainattourism.model.Place;
import th.ac.dusit.dbizcom.chainattourism.net.ApiClient;
import th.ac.dusit.dbizcom.chainattourism.net.GetPlaceResponse;
import th.ac.dusit.dbizcom.chainattourism.net.MyRetrofitCallback;
import th.ac.dusit.dbizcom.chainattourism.net.WebServices;

public class PlaceListFragment extends Fragment {

    private List<Place> mPlaceList = null;

    private PlaceListFragmentListener mListener;

    private View mProgressView;
    private RecyclerView mPlaceListRecyclerView;

    public PlaceListFragment() {
        // Required empty public constructor
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_place_list, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        mPlaceListRecyclerView = view.findViewById(R.id.place_list_recycler_view);
        mProgressView = view.findViewById(R.id.progress_view);

        if (mPlaceList == null) {
            doGetPlace();
        } else {
            setupRecyclerView();
        }
    }

    private void doGetPlace() {
        mProgressView.setVisibility(View.VISIBLE);

        Retrofit retrofit = ApiClient.getClient();
        WebServices services = retrofit.create(WebServices.class);

        Call<GetPlaceResponse> call = services.getPlace();
        call.enqueue(new MyRetrofitCallback<>(
                getActivity(),
                null,
                mProgressView,
                new MyRetrofitCallback.MyRetrofitCallbackListener<GetPlaceResponse>() {
                    @Override
                    public void onSuccess(GetPlaceResponse responseBody) {
                        mPlaceList = responseBody.placeList;
                        setupRecyclerView();
                    }

                    @Override
                    public void onError(String errorMessage) {

                    }
                }
        ));
    }

    private void setupRecyclerView() {
        if (getContext() != null) {
            PlaceListAdapter adapter = new PlaceListAdapter(
                    getContext(),
                    mPlaceList,
                    mListener
            );
            mPlaceListRecyclerView.setLayoutManager(new LinearLayoutManager(getContext()));
            mPlaceListRecyclerView.addItemDecoration(new SpacingDecoration(getContext()));
            mPlaceListRecyclerView.setAdapter(adapter);
        }
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
        if (context instanceof PlaceListFragmentListener) {
            mListener = (PlaceListFragmentListener) context;
        } else {
            throw new RuntimeException(context.toString()
                    + " must implement PlaceListFragmentListener");
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    public interface PlaceListFragmentListener {
        void onClickPlace(Place place);
    }

    private static class PlaceListAdapter extends RecyclerView.Adapter<PlaceListFragment.PlaceListAdapter.PlaceViewHolder> {

        private final Context mContext;
        private final List<Place> mPlaceList;
        private final PlaceListFragmentListener mListener;

        PlaceListAdapter(Context context, List<Place> placeList, PlaceListFragmentListener listener) {
            mContext = context;
            mPlaceList = placeList;
            mListener = listener;
        }

        @NonNull
        @Override
        public PlaceViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext()).inflate(
                    R.layout.item_place, parent, false
            );
            return new PlaceViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull PlaceViewHolder holder, int position) {
            final Place place = mPlaceList.get(position);

            holder.mPlace = place;
            holder.mNameTextView.setText(place.name);
            holder.mDistrictTextView.setText(place.district);
            Glide.with(mContext)
                    .load(ApiClient.IMAGE_BASE_URL + place.listImage)
                    .into(holder.mPlaceImageView);
        }

        @Override
        public int getItemCount() {
            return mPlaceList.size();
        }

        class PlaceViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final TextView mNameTextView;
            private final TextView mDistrictTextView;
            private final ImageView mPlaceImageView;

            private Place mPlace;

            PlaceViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mNameTextView = itemView.findViewById(R.id.place_name_text_view);
                mDistrictTextView = itemView.findViewById(R.id.district_text_view);
                mPlaceImageView = itemView.findViewById(R.id.place_image_view);

                mPlaceImageView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        mListener.onClickPlace(mPlace);
                    }
                });
            }
        }
    }

    public class SpacingDecoration extends RecyclerView.ItemDecoration {

        private final static int MARGIN_IN_DP = 8;
        private final int mMargin;

        SpacingDecoration(@NonNull Context context) {
            DisplayMetrics metrics = context.getResources().getDisplayMetrics();
            mMargin = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_IN_DP,
                    metrics
            );
        }

        @Override
        public void getItemOffsets(@NonNull Rect outRect, @NonNull View view,
                                   @NonNull RecyclerView parent,
                                   @NonNull RecyclerView.State state) {
            final int itemPosition = parent.getChildAdapterPosition(view);
            if (itemPosition == RecyclerView.NO_POSITION) {
                return;
            }
            /*if (itemPosition == 0) {
                outRect.top = mMargin;
            }*/
            final RecyclerView.Adapter adapter = parent.getAdapter();
            if ((adapter != null) && (itemPosition == adapter.getItemCount() - 1)) {
                outRect.bottom = mMargin;
            }
            if ((adapter != null) && (itemPosition == 0)) {
                outRect.top = mMargin;
            }
        }
    }
}
